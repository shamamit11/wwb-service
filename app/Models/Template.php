<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'created_by_user_id',
    'updated_by_user_id',
    'name',
    'slug',
    'template_type',
    'description',
    'status',
    'default_excerpt_prompt',
    'default_meta',
])]
class Template extends Model
{
    use HasUlids, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    public const TYPE_STANDARD = 'standard';

    public const TYPE_TUTORIAL = 'tutorial';

    public const TYPE_LISTICLE = 'listicle';

    public const TYPE_COMPARISON = 'comparison';

    public const TYPE_NEWS = 'news';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_ARCHIVED,
    ];

    public const TEMPLATE_TYPES = [
        self::TYPE_STANDARD,
        self::TYPE_TUTORIAL,
        self::TYPE_LISTICLE,
        self::TYPE_COMPARISON,
        self::TYPE_NEWS,
    ];

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_meta' => 'array',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * @return HasMany<TemplateBlock, $this>
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(TemplateBlock::class)
            ->orderBy('sort_order');
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'template_id');
    }
}
