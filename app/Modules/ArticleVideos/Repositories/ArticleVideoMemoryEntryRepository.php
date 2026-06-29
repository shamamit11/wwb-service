<?php

namespace App\Modules\ArticleVideos\Repositories;

use App\Models\ArticleVideoMemoryEntry;
use App\Modules\ArticleVideos\Data\CreateArticleVideoMemoryEntryData;
use Illuminate\Database\Eloquent\Collection;

interface ArticleVideoMemoryEntryRepository
{
    public function create(CreateArticleVideoMemoryEntryData $data): ArticleVideoMemoryEntry;

    public function findById(int $id): ?ArticleVideoMemoryEntry;

    /**
     * @return Collection<int, ArticleVideoMemoryEntry>
     */
    public function findRecentForPostId(int $postId, int $limit = 10): Collection;
}
