<?php

namespace App\Modules\KnowledgeBase\Services;

use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\LinkKnowledgeBaseEntryToPostData;
use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;
use App\Modules\Posts\Repositories\PostRepository;

class LinkKnowledgeBaseEntryToPostService
{
    public function __construct(
        private readonly KnowledgeBaseEntryRepository $entries,
        private readonly PostRepository $posts,
    ) {}

    public function handle(KnowledgeBaseEntry $entry, LinkKnowledgeBaseEntryToPostData $data): KnowledgeBaseEntry
    {
        $post = $this->posts->findById($data->postId);

        if ($post === null) {
            abort(404);
        }

        $metadata = $entry->metadata ?? [];
        $hooks = $metadata[KnowledgeBaseEntry::LINK_HOOKS_KEY] ?? [];
        $existingPosts = is_array($hooks['posts'] ?? null) ? $hooks['posts'] : [];

        $existingPosts = array_values(array_filter($existingPosts, fn ($hook): bool => ! is_array($hook)
            || ! isset($hook['id'])
            || (int) $hook['id'] !== $post->id));

        $existingPosts[] = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
        ];

        $hooks['posts'] = array_values($existingPosts);
        $metadata[KnowledgeBaseEntry::LINK_HOOKS_KEY] = $hooks;

        return $this->entries->updateMetadata($entry, $metadata);
    }
}
