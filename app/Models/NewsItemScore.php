<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'news_item_id',
    'relevance_score',
    'freshness_score',
    'credibility_score',
    'pillar_fit_score',
    'evergreen_potential_score',
    'novelty_score',
    'business_value_score',
    'total_score',
    'decision',
    'reasoning',
    'scored_at',
])]
class NewsItemScore extends Model
{
    public const DECISION_IGNORE = 'ignore';

    public const DECISION_KNOWLEDGE_BASE = 'knowledge_base';

    public const DECISION_TOPIC = 'topic';

    public const DECISION_KNOWLEDGE_BASE_AND_TOPIC = 'knowledge_base_and_topic';

    public const DECISIONS = [
        self::DECISION_IGNORE,
        self::DECISION_KNOWLEDGE_BASE,
        self::DECISION_TOPIC,
        self::DECISION_KNOWLEDGE_BASE_AND_TOPIC,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scored_at' => 'datetime',
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
