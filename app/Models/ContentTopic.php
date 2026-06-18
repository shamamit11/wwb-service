<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'title',
    'slug',
    'cluster',
    'primary_keyword',
    'secondary_keywords',
    'search_intent',
    'priority_score',
    'difficulty_note',
    'source',
    'status',
    'notes',
    'approved_at',
    'rejected_at',
    'used_at',
])]
class ContentTopic extends Model
{
    public const STATUS_SUGGESTED = 'suggested';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_USED = 'used';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_AI_SUGGESTED = 'ai_suggested';

    public const CLUSTER_AI_TOOLS = 'ai_tools';

    public const CLUSTER_AI_FOR_BLOGGING = 'ai_for_blogging';

    public const CLUSTER_SEO = 'seo';

    public const CLUSTER_CONTENT_MARKETING = 'content_marketing';

    public const CLUSTER_PRODUCTIVITY_AUTOMATION = 'productivity_automation';

    public const CLUSTER_DEVELOPER_AI = 'developer_ai';

    public const STATUSES = [
        self::STATUS_SUGGESTED,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_USED,
    ];

    public const CLUSTERS = [
        self::CLUSTER_AI_TOOLS,
        self::CLUSTER_AI_FOR_BLOGGING,
        self::CLUSTER_SEO,
        self::CLUSTER_CONTENT_MARKETING,
        self::CLUSTER_PRODUCTIVITY_AUTOMATION,
        self::CLUSTER_DEVELOPER_AI,
    ];

    public const SOURCES = [
        self::SOURCE_MANUAL,
        self::SOURCE_AI_SUGGESTED,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'secondary_keywords' => 'array',
            'priority_score' => 'decimal:2',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canGenerateContentBrief(): bool
    {
        return $this->isApproved();
    }

    /**
     * @return HasOne<ContentBrief, $this>
     */
    public function contentBrief(): HasOne
    {
        return $this->hasOne(ContentBrief::class, 'content_topic_id');
    }
}
