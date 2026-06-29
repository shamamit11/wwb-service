<?php

namespace App\Modules\ArticleVideos\Repositories;

use App\Models\ArticleVideoMemoryEntry;
use App\Modules\ArticleVideos\Data\CreateArticleVideoMemoryEntryData;
use Illuminate\Database\Eloquent\Collection;

class EloquentArticleVideoMemoryEntryRepository implements ArticleVideoMemoryEntryRepository
{
    public function create(CreateArticleVideoMemoryEntryData $data): ArticleVideoMemoryEntry
    {
        $entry = ArticleVideoMemoryEntry::query()->create([
            'post_id' => $data->postId,
            'article_video_id' => $data->articleVideoId,
            'hook' => $data->hook,
            'core_angle' => $data->coreAngle,
            'voiceover_text' => $data->voiceoverText,
        ]);

        return $this->refreshWithRelations($entry);
    }

    public function findById(int $id): ?ArticleVideoMemoryEntry
    {
        return ArticleVideoMemoryEntry::query()
            ->with($this->relations())
            ->find($id);
    }

    /**
     * @return Collection<int, ArticleVideoMemoryEntry>
     */
    public function findRecentForPostId(int $postId, int $limit = 10): Collection
    {
        return ArticleVideoMemoryEntry::query()
            ->with($this->relations())
            ->where('post_id', $postId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(max(1, $limit))
            ->get();
    }

    private function refreshWithRelations(ArticleVideoMemoryEntry $entry): ArticleVideoMemoryEntry
    {
        return $entry->refresh()->load($this->relations());
    }

    /**
     * @return list<string>
     */
    private function relations(): array
    {
        return ['post', 'articleVideo'];
    }
}
