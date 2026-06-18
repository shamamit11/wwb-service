<?php

namespace App\Modules\ContentBriefs\Services;

use App\Models\ContentBrief;
use App\Modules\ContentBriefs\Data\UpdateContentBriefData;
use App\Modules\ContentBriefs\Exceptions\InvalidContentBriefStateTransitionException;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Support\AuditActivityLogger;

class ApproveContentBriefService
{
    public function __construct(
        private readonly ContentBriefRepository $briefs,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(ContentBrief $brief): ContentBrief
    {
        if (! in_array($brief->status, [ContentBrief::STATUS_DRAFT, ContentBrief::STATUS_REJECTED], true)) {
            throw new InvalidContentBriefStateTransitionException(
                action: 'approve',
                currentStatus: $brief->status,
                message: "Content brief cannot be approved from [{$brief->status}] status.",
            );
        }

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
