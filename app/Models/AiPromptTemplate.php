<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'key',
    'type',
    'description',
    'status',
    'active_version_id',
])]
class AiPromptTemplate extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    public const TYPE_TOPIC_DISCOVERY = 'topic_discovery';

    public const TYPE_CONTENT_BRIEF = 'content_brief';

    public const TYPE_BLOG_WRITER = 'blog_writer';

    public const TYPE_EDITOR = 'editor';

    public const TYPE_SEO_OPTIMIZER = 'seo_optimizer';

    public const TYPE_EDITORIAL_REFINER = 'editorial_refiner';

    public const TYPE_PUBLISHING = 'publishing';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_ARCHIVED,
    ];

    public const TYPES = [
        self::TYPE_TOPIC_DISCOVERY,
        self::TYPE_CONTENT_BRIEF,
        self::TYPE_BLOG_WRITER,
        self::TYPE_EDITOR,
        self::TYPE_SEO_OPTIMIZER,
        self::TYPE_EDITORIAL_REFINER,
        self::TYPE_PUBLISHING,
    ];

    /**
     * @return HasMany<AiPromptTemplateVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(AiPromptTemplateVersion::class, 'prompt_template_id')
            ->orderByDesc('version');
    }

    /**
     * @return BelongsTo<AiPromptTemplateVersion, $this>
     */
    public function activeVersion(): BelongsTo
    {
        return $this->belongsTo(AiPromptTemplateVersion::class, 'active_version_id');
    }
}
