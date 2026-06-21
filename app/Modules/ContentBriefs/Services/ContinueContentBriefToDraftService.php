<?php

namespace App\Modules\ContentBriefs\Services;

use App\Models\ContentBrief;
use App\Models\Post;
use App\Modules\Ai\Services\DraftGenerationWorkflow;
use App\Modules\Ai\Services\ResolveAutoDraftGenerationDataService;
use App\Modules\ContentBriefs\Data\ContinueContentBriefToDraftResultData;
use App\Modules\ContentBriefs\Data\UpdateContentBriefData;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Modules\Posts\Repositories\PostRepository;
use App\Support\AuditActivityLogger;

class ContinueContentBriefToDraftService
{
    public const STATUS_DRAFT_QUEUED = 'draft_queued';

    public const STATUS_APPROVED_WITHOUT_DRAFT_QUEUE = 'approved_without_draft_queue';

    public const STATUS_EXISTING_POST = 'existing_post';

    public const STATUS_ALREADY_USED = 'already_used';

    public function __construct(
        private readonly ContentBriefRepository $briefs,
        private readonly AuditActivityLogger $audit,
        private readonly ResolveAutoDraftGenerationDataService $resolveAutoDraftData,
        private readonly DraftGenerationWorkflow $draftGeneration,
        private readonly PostRepository $posts,
    ) {}

    public function handle(ContentBrief $brief): ContinueContentBriefToDraftResultData
    {
        $brief = $brief->loadMissing('topic');

        $existingPost = $this->posts->findBySourceContentBriefId((int) $brief->id);

        if ($existingPost instanceof Post) {
            return new ContinueContentBriefToDraftResultData(
                brief: $brief,
                status: self::STATUS_EXISTING_POST,
                postId: (int) $existingPost->id,
            );
        }

        if ($brief->status === ContentBrief::STATUS_USED) {
            return new ContinueContentBriefToDraftResultData(
                brief: $brief,
                status: self::STATUS_ALREADY_USED,
            );
        }

        $autoDraftData = $this->resolveAutoDraftData->handle($brief);

        if (in_array($brief->status, [ContentBrief::STATUS_DRAFT, ContentBrief::STATUS_REJECTED], true)) {
            $brief = $this->approveBrief($brief);

            if ($autoDraftData === null) {
                return new ContinueContentBriefToDraftResultData(
                    brief: $brief,
                    status: self::STATUS_APPROVED_WITHOUT_DRAFT_QUEUE,
                );
            }

            $job = $this->draftGeneration->queue($brief, $autoDraftData);

            return new ContinueContentBriefToDraftResultData(
                brief: $brief,
                status: self::STATUS_DRAFT_QUEUED,
                aiJobId: (int) $job->id,
            );
        }

        if ($brief->status === ContentBrief::STATUS_APPROVED) {
            if ($autoDraftData === null) {
                return new ContinueContentBriefToDraftResultData(
                    brief: $brief,
                    status: self::STATUS_APPROVED_WITHOUT_DRAFT_QUEUE,
                );
            }

            $job = $this->draftGeneration->queue($brief, $autoDraftData);

            return new ContinueContentBriefToDraftResultData(
                brief: $brief,
                status: self::STATUS_DRAFT_QUEUED,
                aiJobId: (int) $job->id,
            );
        }

        return new ContinueContentBriefToDraftResultData(
            brief: $brief,
            status: self::STATUS_APPROVED_WITHOUT_DRAFT_QUEUE,
        );
    }
    /**
     * Keep this local to avoid a service-container cycle with ApproveContentBriefService.
     */
    private function approveBrief(ContentBrief $brief): ContentBrief
    {
        $old = $this->auditAttributes($brief);

        $updated = $this->briefs->update($brief, new UpdateContentBriefData(
            title: $brief->title,
            slug: $brief->slug,
            metaTitle: $brief->meta_title,
            metaDescription: $brief->meta_description,
            primaryKeyword: $brief->primary_keyword,
            secondaryKeywords: $brief->secondary_keywords ?? [],
            searchIntent: $brief->search_intent,
            outline: $brief->outline ?? [],
            headings: $brief->headings ?? [],
            faqSuggestions: $brief->faq_suggestions ?? [],
            internalLinkSuggestions: $brief->internal_link_suggestions ?? [],
            imageSuggestions: $brief->image_suggestions ?? [],
            status: ContentBrief::STATUS_APPROVED,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'content-brief.approved',
            event: 'approved',
            subject: $updated,
            attributes: $this->auditAttributes($updated),
            old: $old,
        );

        return $updated;
    }

    /**
     * @return array<string, mixed>
     */
    private function auditAttributes(ContentBrief $brief): array
    {
        return [
            'status' => $brief->status,
            'approved_at' => $brief->approved_at?->toISOString(),
        ];
    }
}
