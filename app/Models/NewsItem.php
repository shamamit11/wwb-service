<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'external_id',
    'provider',
    'source_id',
    'category_id',
    'publisher_name',
    'title',
    'normalized_title',
    'url',
    'canonical_url',
    'description',
    'author',
    'language',
    'country',
    'published_at',
    'discovered_at',
    'status',
    'metadata',
])]
class NewsItem extends Model
{
    public const STATUS_DISCOVERED = 'discovered';

    public const STATUS_SCREENED = 'screened';

    public const STATUS_EXTRACTED = 'extracted';

    public const STATUS_ROUTED = 'routed';

    public const STATUS_IGNORED = 'ignored';

    public const STATUS_FAILED = 'failed';

    public const STATUSES = [
        self::STATUS_DISCOVERED,
        self::STATUS_SCREENED,
        self::STATUS_EXTRACTED,
        self::STATUS_ROUTED,
        self::STATUS_IGNORED,
        self::STATUS_FAILED,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'discovered_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<NewsSource, $this>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class, 'source_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id')->withTrashed();
    }

    /**
     * @return HasMany<NewsItemExtraction, $this>
     */
    public function extractions(): HasMany
    {
        return $this->hasMany(NewsItemExtraction::class, 'news_item_id')->orderByDesc('id');
    }

    /**
     * @return HasMany<NewsItemScore, $this>
     */
    public function scores(): HasMany
    {
        return $this->hasMany(NewsItemScore::class, 'news_item_id')->orderByDesc('id');
    }

    /**
     * @return HasMany<NewsItemRoute, $this>
     */
    public function routes(): HasMany
    {
        return $this->hasMany(NewsItemRoute::class, 'news_item_id')->orderByDesc('id');
    }

    /**
     * @return HasOne<NewsItemExtraction, $this>
     */
    public function latestExtraction(): HasOne
    {
        return $this->hasOne(NewsItemExtraction::class, 'news_item_id')->latestOfMany();
    }

    /**
     * @return HasOne<NewsItemScore, $this>
     */
    public function latestScore(): HasOne
    {
        return $this->hasOne(NewsItemScore::class, 'news_item_id')->latestOfMany();
    }

    /**
     * @return HasOne<NewsItemRoute, $this>
     */
    public function latestRoute(): HasOne
    {
        return $this->hasOne(NewsItemRoute::class, 'news_item_id')->latestOfMany();
    }
}
