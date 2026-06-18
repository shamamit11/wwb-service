<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'content_topic_id',
    'title',
    'slug',
    'meta_title',
    'meta_description',
    'primary_keyword',
    'secondary_keywords',
    'search_intent',
    'outline',
    'headings',
    'faq_suggestions',
    'internal_link_suggestions',
    'image_suggestions',
    'status',
    'approved_at',
])]
class ContentBrief extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_USED = 'used';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_USED,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'secondary_keywords' => 'array',
            'outline' => 'array',
            'headings' => 'array',
            'faq_suggestions' => 'array',
            'internal_link_suggestions' => 'array',
            'image_suggestions' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ContentTopic, $this>
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ContentTopic::class, 'content_topic_id');
    }

    public function canGenerateDraft(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
