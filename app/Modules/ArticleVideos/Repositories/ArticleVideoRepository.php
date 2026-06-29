<?php

namespace App\Modules\ArticleVideos\Repositories;

use App\Models\ArticleVideo;
use App\Modules\ArticleVideos\Data\CreateArticleVideoData;
use App\Modules\ArticleVideos\Data\UpdateArticleVideoData;
use Illuminate\Database\Eloquent\Collection;

interface ArticleVideoRepository
{
    public function create(CreateArticleVideoData $data): ArticleVideo;

    public function update(ArticleVideo $video, UpdateArticleVideoData $data): ArticleVideo;

    public function findById(int $id): ?ArticleVideo;

    public function findActiveDraftForPostId(int $postId): ?ArticleVideo;

    public function hasRenderedVideoForPostId(int $postId): bool;

    /**
     * @return Collection<int, ArticleVideo>
     */
    public function findByPostId(int $postId): Collection;
}
