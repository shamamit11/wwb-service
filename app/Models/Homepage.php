<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'singleton_key',
    'hero',
    'featured_editorial',
    'guide_section',
    'topic_section',
    'promo_section',
    'newsletter_section',
    'seo',
    'updated_by_user_id',
])]
class Homepage extends Model
{
    use HasUlids;

    public const SINGLETON_KEY = 'default';

    public const SECTION_MODE_MANUAL = 'manual';

    public const SECTION_MODE_AUTOMATIC = 'automatic';

    public const SECTION_MODES = [
        self::SECTION_MODE_MANUAL,
        self::SECTION_MODE_AUTOMATIC,
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
            'hero' => 'array',
            'featured_editorial' => 'array',
            'guide_section' => 'array',
            'topic_section' => 'array',
            'promo_section' => 'array',
            'newsletter_section' => 'array',
            'seo' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultPayload(): array
    {
        return [
            'hero' => [
                'eyebrow' => null,
                'title' => null,
                'description' => null,
                'primary_cta_label' => null,
                'primary_cta_url' => null,
                'secondary_cta_label' => null,
                'secondary_cta_url' => null,
                'media_url' => null,
                'media_alt' => null,
            ],
            'featured_editorial' => [
                'title' => null,
                'description' => null,
                'mode' => self::SECTION_MODE_MANUAL,
                'post_ids' => [],
                'category_ids' => null,
                'limit' => null,
            ],
            'guide_section' => [
                'title' => null,
                'description' => null,
                'mode' => self::SECTION_MODE_MANUAL,
                'post_ids' => [],
                'category_ids' => null,
                'limit' => null,
            ],
            'topic_section' => [
                'title' => null,
                'description' => null,
                'category_ids' => [],
            ],
            'promo_section' => [
                'enabled' => false,
                'eyebrow' => null,
                'title' => null,
                'description' => null,
                'bullet_points' => [],
                'primary_cta_label' => null,
                'primary_cta_url' => null,
                'stats' => [],
            ],
            'newsletter_section' => [
                'enabled' => false,
                'title' => null,
                'description' => null,
            ],
            'seo' => [
                'meta_title' => null,
                'meta_description' => null,
            ],
        ];
    }
}
