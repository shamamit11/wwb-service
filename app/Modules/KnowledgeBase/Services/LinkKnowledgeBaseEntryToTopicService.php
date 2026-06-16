<?php

namespace App\Modules\KnowledgeBase\Services;

use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\LinkKnowledgeBaseEntryToTopicData;
use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;

class LinkKnowledgeBaseEntryToTopicService
{
    public function __construct(
        private readonly KnowledgeBaseEntryRepository $entries,
    ) {}

    public function handle(KnowledgeBaseEntry $entry, LinkKnowledgeBaseEntryToTopicData $data): KnowledgeBaseEntry
    {
        $metadata = $entry->metadata ?? [];
        $hooks = $metadata[KnowledgeBaseEntry::LINK_HOOKS_KEY] ?? [];
        $existingTopics = is_array($hooks['topics'] ?? null) ? $hooks['topics'] : [];

        $existingTopics = array_values(array_filter($existingTopics, fn ($hook): bool => ! is_array($hook)
            || ! isset($hook['id'])
            || (int) $hook['id'] !== $data->topicId));

        $existingTopics[] = [
            'id' => $data->topicId,
        ];

        $hooks['topics'] = array_values($existingTopics);
        $metadata[KnowledgeBaseEntry::LINK_HOOKS_KEY] = $hooks;

        return $this->entries->updateMetadata($entry, $metadata);
    }
}
