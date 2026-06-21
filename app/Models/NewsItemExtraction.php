<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'news_item_id',
    'extractor',
    'content_markdown',
    'content_text',
    'excerpt',
    'facts_json',
    'entities_json',
    'claims_json',
    'extracted_at',
    'metadata',
])]
class NewsItemExtraction extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'facts_json' => 'array',
            'entities_json' => 'array',
            'claims_json' => 'array',
            'extracted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<NewsItem, $this>
     */
    public function newsItem(): BelongsTo
    {
        return $this->belongsTo(NewsItem::class, 'news_item_id');
    }
}
