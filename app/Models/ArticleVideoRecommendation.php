<?php

namespace App\Models;

use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationFormat;
use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationPriority;
use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'post_id',
    'score',
    'priority',
    'recommended_format',
    'reason',
    'suggested_hook',
    'risk_note',
    'status',
    'evaluated_at',
])]
class ArticleVideoRecommendation extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'post_id' => 'integer',
            'score' => 'integer',
            'priority' => ArticleVideoRecommendationPriority::class,
            'recommended_format' => ArticleVideoRecommendationFormat::class,
            'status' => ArticleVideoRecommendationStatus::class,
            'evaluated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id')->withTrashed();
    }

    /**
     * @return HasMany<ArticleVideo, $this>
     */
    public function videos(): HasMany
    {
        return $this->hasMany(ArticleVideo::class, 'article_video_recommendation_id')
            ->orderByDesc('id');
    }

    public function isPending(): bool
    {
        return $this->status === ArticleVideoRecommendationStatus::Pending;
    }

    public function isSelected(): bool
    {
        return $this->status === ArticleVideoRecommendationStatus::Selected;
    }

    public function isSkipped(): bool
    {
        return $this->status === ArticleVideoRecommendationStatus::Skipped;
    }

    public function isRejected(): bool
    {
        return $this->status === ArticleVideoRecommendationStatus::Rejected;
    }
}
