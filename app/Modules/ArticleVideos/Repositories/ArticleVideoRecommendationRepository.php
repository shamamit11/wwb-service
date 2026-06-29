<?php

namespace App\Modules\ArticleVideos\Repositories;

use App\Models\ArticleVideoRecommendation;
use App\Modules\ArticleVideos\Data\CreateArticleVideoRecommendationData;
use App\Modules\ArticleVideos\Data\RefreshPendingArticleVideoRecommendationData;
use App\Modules\ArticleVideos\Data\UpdateArticleVideoRecommendationData;
use Illuminate\Database\Eloquent\Collection;

interface ArticleVideoRecommendationRepository
{
    public function create(CreateArticleVideoRecommendationData $data): ArticleVideoRecommendation;

    public function update(ArticleVideoRecommendation $recommendation, UpdateArticleVideoRecommendationData $data): ArticleVideoRecommendation;

    public function findById(int $id): ?ArticleVideoRecommendation;

    public function findLatestPendingByPostId(int $postId): ?ArticleVideoRecommendation;

    public function upsertPendingForPost(RefreshPendingArticleVideoRecommendationData $data): ArticleVideoRecommendation;

    /**
     * @return Collection<int, ArticleVideoRecommendation>
     */
    public function findByPostId(int $postId): Collection;
}
