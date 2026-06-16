<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'created_by_user_id',
    'updated_by_user_id',
    'title',
    'slug',
    'entry_type',
    'status',
    'summary',
    'content_markdown',
    'source_url',
    'featured_media_id',
    'metadata',
])]
class KnowledgeBaseEntry extends Model
{
    use HasUlids, SoftDeletes;

    public const LINK_HOOKS_KEY = 'link_hooks';

    public const TYPE_NOTE = 'note';

    public const TYPE_RESEARCH = 'research';

    public const TYPE_EXPERIENCE = 'experience';

    public const TYPE_ARCHITECTURE = 'architecture';

    public const TYPE_CODE = 'code';

    public const TYPE_REFERENCE = 'reference';

    public const TYPE_IDEA = 'idea';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    public const ENTRY_TYPES = [
        self::TYPE_NOTE,
        self::TYPE_RESEARCH,
        self::TYPE_EXPERIENCE,
        self::TYPE_ARCHITECTURE,
        self::TYPE_CODE,
        self::TYPE_REFERENCE,
        self::TYPE_IDEA,
    ];

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_ARCHIVED,
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
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
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
     * @return BelongsTo<Media, $this>
     */
    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id')->withTrashed();
    }

    /**
     * @return list<array{id:int,title:string,slug:string}>
     */
    public function linkedPosts(): array
    {
        $hooks = $this->metadata[self::LINK_HOOKS_KEY]['posts'] ?? [];

        if (! is_array($hooks)) {
            return [];
        }

        return array_values(array_filter($hooks, fn ($hook): bool => is_array($hook)
            && isset($hook['id'], $hook['title'], $hook['slug'])
            && is_int($hook['id'])
            && is_string($hook['title'])
            && is_string($hook['slug'])));
    }

    /**
     * @return list<array{id:int}>
     */
    public function linkedTopics(): array
    {
        $hooks = $this->metadata[self::LINK_HOOKS_KEY]['topics'] ?? [];

        if (! is_array($hooks)) {
            return [];
        }

        return array_values(array_filter($hooks, fn ($hook): bool => is_array($hook)
            && isset($hook['id'])
            && is_int($hook['id'])));
    }

    /**
     * @return MorphOne<SeoMetadata, $this>
     */
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'seoable');
    }
}
