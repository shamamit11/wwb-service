<?php

namespace App\Modules\ContentBriefs\Services;

use App\Models\ContentBrief;
use App\Modules\ContentBriefs\Data\UpdateContentBriefData;
use App\Modules\ContentBriefs\Exceptions\InvalidContentBriefStateTransitionException;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Support\AuditActivityLogger;

class UpdateContentBriefService
{
    public function __construct(
        private readonly ContentBriefRepository $briefs,
        private readonly ContentBriefSlugResolver $slugResolver,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(ContentBrief $brief, UpdateContentBriefData $data): ContentBrief
    {
        $this->guardStatusTransition($brief, $data->status);

        $old = $this->auditAttributes($brief);
        $title = $data->title ?? $brief->title;
        $slug = $this->slugResolver->resolve($title, $data->slug ?? $brief->slug, (int) $brief->id);

        $updated = $this->briefs->update($brief, new UpdateContentBriefData(
            title: $title,
            slug: $slug,
            metaTitle: $data->metaTitle ?? $brief->meta_title,
            metaDescription: $data->metaDescription ?? $brief->meta_description,
            primaryKeyword: $data->primaryKeyword ?? $brief->primary_keyword,
            secondaryKeywords: $data->secondaryKeywords ?? ($brief->secondary_keywords ?? []),
            searchIntent: $data->searchIntent ?? $brief->search_intent,
            outline: $data->outline ?? ($brief->outline ?? []),
            headings: $data->headings ?? ($brief->headings ?? []),
            faqSuggestions: $data->faqSuggestions ?? ($brief->faq_suggestions ?? []),
            internalLinkSuggestions: $data->internalLinkSuggestions ?? ($brief->internal_link_suggestions ?? []),
            imageSuggestions: $data->imageSuggestions ?? ($brief->image_suggestions ?? []),
            status: $data->status ?? $brief->status,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'content-brief.updated',
            event: 'updated',
            subject: $updated,
            attributes: $this->auditAttributes($updated),
            old: $old,
        );

        return $updated;
    }

    private function guardStatusTransition(ContentBrief $brief, ?string $targetStatus): void
    {
        if ($targetStatus === null || $targetStatus === $brief->status) {
            return;
        }

        $allowed = match ($targetStatus) {
            ContentBrief::STATUS_DRAFT => [ContentBrief::STATUS_REJECTED, ContentBrief::STATUS_APPROVED],
            ContentBrief::STATUS_REJECTED => [ContentBrief::STATUS_DRAFT, ContentBrief::STATUS_APPROVED],
            ContentBrief::STATUS_USED => [ContentBrief::STATUS_APPROVED],
            default => [],
        };

        if (in_array($brief->status, $allowed, true)) {
            return;
        }

        throw new InvalidContentBriefStateTransitionException(
            action: "update-status:{$targetStatus}",
            currentStatus: $brief->status,
            message: "Content brief cannot move to [{$targetStatus}] from [{$brief->status}] status.",
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function auditAttributes(ContentBrief $brief): array
    {
        return [
            'content_topic_id' => $brief->content_topic_id,
            'title' => $brief->title,
            'slug' => $brief->slug,
            'status' => $brief->status,
            'approved_at' => $brief->approved_at?->toISOString(),
        ];
    }
}
