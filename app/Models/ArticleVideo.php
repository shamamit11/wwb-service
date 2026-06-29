<?php

namespace App\Models;

use App\Modules\ArticleVideos\Enums\ArticleVideoRenderMode;
use App\Modules\ArticleVideos\Enums\ArticleVideoStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'post_id',
    'article_video_recommendation_id',
    'status',
    'render_mode',
    'hook',
    'voiceover_text',
    'script_json',
    'captions_json',
    'voiceover_path',
    'captions_path',
    'thumbnail_path',
    'video_path',
    'duration_seconds',
    'approved_at',
    'rendered_at',
    'error_message',
])]
class ArticleVideo extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'post_id' => 'integer',
            'article_video_recommendation_id' => 'integer',
            'status' => ArticleVideoStatus::class,
            'render_mode' => ArticleVideoRenderMode::class,
            'script_json' => 'array',
            'captions_json' => 'array',
            'duration_seconds' => 'integer',
            'approved_at' => 'datetime',
            'rendered_at' => 'datetime',
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
     * @return BelongsTo<ArticleVideoRecommendation, $this>
     */
    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(ArticleVideoRecommendation::class, 'article_video_recommendation_id');
    }

    /**
     * @return HasMany<ArticleVideoMemoryEntry, $this>
     */
    public function memoryEntries(): HasMany
    {
        return $this->hasMany(ArticleVideoMemoryEntry::class, 'article_video_id')
            ->orderByDesc('id');
    }

    public function isDraft(): bool
    {
        return $this->status === ArticleVideoStatus::Draft;
    }

    public function isApproved(): bool
    {
        return $this->status === ArticleVideoStatus::Approved;
    }

    public function isRendered(): bool
    {
        return $this->status === ArticleVideoStatus::Rendered;
    }

    public function isFailed(): bool
    {
        return $this->status === ArticleVideoStatus::Failed;
    }

    public function canRegenerateDraft(): bool
    {
        return $this->status === ArticleVideoStatus::Draft;
    }

    public function canRender(): bool
    {
        return $this->status === ArticleVideoStatus::Approved;
    }
}
