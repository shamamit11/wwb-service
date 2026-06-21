<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'kind',
    'base_url',
    'trust_score',
    'is_active',
    'metadata',
])]
class NewsSource extends Model
{
    public const KIND_API = 'api';

    public const KIND_PUBLISHER = 'publisher';

    public const KIND_RSS = 'rss';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trust_score' => 'decimal:2',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * @return HasMany<NewsItem, $this>
     */
    public function newsItems(): HasMany
    {
        return $this->hasMany(NewsItem::class, 'source_id');
    }
}
