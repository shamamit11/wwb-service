<?php

namespace App\Modules\ArticleVideos\Repositories;

use App\Models\ArticleVideoRecommendation;
use App\Modules\ArticleVideos\Data\CreateArticleVideoRecommendationData;
use App\Modules\ArticleVideos\Data\RefreshPendingArticleVideoRecommendationData;
use App\Modules\ArticleVideos\Data\UpdateArticleVideoRecommendationData;
use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationStatus;
use Illuminate\Database\Eloquent\Collection;

class EloquentArticleVideoRecommendationRepository implements ArticleVideoRecommendationRepository
{
    public function create(CreateArticleVideoRecommendationData $data): ArticleVideoRecommendation
    {
        $recommendation = ArticleVideoRecommendation::query()->create([
            'post_id' => $data->postId,
            'score' => $data->score,
            'priority' => $data->priority,
            'recommended_format' => $data->recommendedFormat,
            'reason' => $data->reason,
            'suggested_hook' => $data->suggestedHook,
            'risk_note' => $data->riskNote,
            'status' => $data->status,
            'evaluated_at' => $data->evaluatedAt,
        ]);

        return $this->refreshWithRelations($recommendation);
    }

    public function update(ArticleVideoRecommendation $recommendation, UpdateArticleVideoRecommendationData $data): ArticleVideoRecommendation
    {
        $recommendation->update([
            'score' => $data->score,
            'priority' => $data->priority,
            'recommended_format' => $data->recommendedFormat,
            'reason' => $data->reason,
            'suggested_hook' => $data->suggestedHook,
            'risk_note' => $data->riskNote,
            'status' => $data->status,
            'evaluated_at' => $data->evaluatedAt,
        ]);

        return $this->refreshWithRelations($recommendation);
    }

    public function findById(int $id): ?ArticleVideoRecommendation
    {
        return ArticleVideoRecommendation::query()
            ->with($this->relations())
            ->find($id);
    }

    public function findLatestPendingByPostId(int $postId): ?ArticleVideoRecommendation
    {
        return ArticleVideoRecommendation::query()
            ->with($this->relations())
            ->where('post_id', $postId)
            ->where('status', ArticleVideoRecommendationStatus::Pending->value)
            ->latest('id')
            ->first();
    }

    public function upsertPendingForPost(RefreshPendingArticleVideoRecommendationData $data): ArticleVideoRecommendation
    {
        $recommendation = $this->findLatestPendingByPostId($data->postId);

        if ($recommendation instanceof ArticleVideoRecommendation) {
            return $this->update($recommendation, new UpdateArticleVideoRecommendationData(
                score: $data->score,
                priority: $data->priority,
                recommendedFormat: $data->recommendedFormat,
                reason: $data->reason,
                suggestedHook: $data->suggestedHook,
                riskNote: $data->riskNote,
                status: ArticleVideoRecommendationStatus::Pending,
                evaluatedAt: $data->evaluatedAt,
            ));
        }

        return $this->create(new CreateArticleVideoRecommendationData(
            postId: $data->postId,
            score: $data->score,
            priority: $data->priority,
            recommendedFormat: $data->recommendedFormat,
            reason: $data->reason,
            suggestedHook: $data->suggestedHook,
            riskNote: $data->riskNote,
            status: ArticleVideoRecommendationStatus::Pending,
            evaluatedAt: $data->evaluatedAt,
        ));
    }

    /**
     * @return Collection<int, ArticleVideoRecommendation>
     */
    public function findByPostId(int $postId): Collection
    {
        return ArticleVideoRecommendation::query()
            ->with($this->relations())
            ->where('post_id', $postId)
            ->orderByDesc('id')
            ->get();
    }

    private function refreshWithRelations(ArticleVideoRecommendation $recommendation): ArticleVideoRecommendation
    {
        return $recommendation->refresh()->load($this->relations());
    }

    /**
     * @return list<string>
     */
    private function relations(): array
    {
        return ['post', 'videos'];
    }
}
