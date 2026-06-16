<?php

namespace App\Modules\KnowledgeBase\Services;

use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\UpdateKnowledgeBaseEntryData;
use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;

class UpdateKnowledgeBaseEntryService
{
    public function __construct(
        private readonly KnowledgeBaseEntryRepository $entries,
        private readonly KnowledgeBaseEntrySlugResolver $slugResolver,
    ) {}

    public function handle(KnowledgeBaseEntry $entry, UpdateKnowledgeBaseEntryData $data): KnowledgeBaseEntry
    {
        return $this->entries->update($entry, new UpdateKnowledgeBaseEntryData(
            updatedByUserId: $data->updatedByUserId,
            title: $data->title,
            slug: $this->slugResolver->resolve($data->title, $data->slug, $entry->id),
            entryType: $data->entryType,
            status: $data->status,
            summary: $data->summary,
            contentMarkdown: $data->contentMarkdown,
            sourceUrl: $data->sourceUrl,
            featuredMediaId: $data->featuredMediaId,
            metadata: $this->mergeReservedMetadata($entry, $data->metadata),
        ));
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     * @return array<string, mixed>|null
     */
    private function mergeReservedMetadata(KnowledgeBaseEntry $entry, ?array $metadata): ?array
    {
        $existingLinkHooks = $entry->metadata[KnowledgeBaseEntry::LINK_HOOKS_KEY] ?? null;
        $merged = $metadata ?? [];

        if ($existingLinkHooks !== null) {
            $merged[KnowledgeBaseEntry::LINK_HOOKS_KEY] = $existingLinkHooks;
        }

        return $merged === [] ? null : $merged;
    }
}
