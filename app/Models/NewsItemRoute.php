<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'news_item_id',
    'route',
    'knowledge_base_entry_id',
    'content_topic_id',
    'post_id',
    'routed_at',
    'metadata',
])]
class NewsItemRoute extends Model
{
    public const ROUTE_IGNORE = 'ignore';

    public const ROUTE_KNOWLEDGE_BASE = 'knowledge_base';

    public const ROUTE_TOPIC = 'topic';

    public const ROUTE_KNOWLEDGE_BASE_AND_TOPIC = 'knowledge_base_and_topic';

    public const ROUTES = [
        self::ROUTE_IGNORE,
        self::ROUTE_KNOWLEDGE_BASE,
        self::ROUTE_TOPIC,
        self::ROUTE_KNOWLEDGE_BASE_AND_TOPIC,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'routed_at' => 'datetime',
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

    /**
     * @return BelongsTo<KnowledgeBaseEntry, $this>
     */
    public function knowledgeBaseEntry(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseEntry::class, 'knowledge_base_entry_id');
    }

    /**
     * @return BelongsTo<ContentTopic, $this>
     */
    public function contentTopic(): BelongsTo
    {
        return $this->belongsTo(ContentTopic::class, 'content_topic_id');
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
