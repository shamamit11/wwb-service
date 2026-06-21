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

    public const TYPE_BLOG_WRITER = 'blog_writer';

    public const KEY_TOPIC_STANDARD = 'topic_standard';

    public const KEY_BLOG_STANDARD = 'blog_standard';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_ARCHIVED,
    ];

    public const MANAGED_TYPES = [
        self::TYPE_TOPIC_DISCOVERY,
        self::TYPE_BLOG_WRITER,
    ];

    public const MANAGED_KEYS = [
        self::KEY_TOPIC_STANDARD,
        self::KEY_BLOG_STANDARD,
    ];

    public const MANAGED_KEY_TYPE_MAP = [
        self::KEY_TOPIC_STANDARD => self::TYPE_TOPIC_DISCOVERY,
        self::KEY_BLOG_STANDARD => self::TYPE_BLOG_WRITER,
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
