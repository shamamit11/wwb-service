<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'singleton_key',
    'hero',
    'mission_section',
    'stats_section',
    'values_section',
    'team_section',
    'seo',
    'updated_by_user_id',
])]
class AboutPage extends Model
{
    use HasUlids;

    public const SINGLETON_KEY = 'default';

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
            'mission_section' => 'array',
            'stats_section' => 'array',
            'values_section' => 'array',
            'team_section' => 'array',
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
                'media_url' => null,
                'media_alt' => null,
            ],
            'mission_section' => [
                'title' => null,
                'description' => null,
                'quote' => null,
            ],
            'stats_section' => [
                'items' => [],
            ],
            'values_section' => [
                'title' => null,
                'items' => [],
            ],
            'team_section' => [
                'title' => null,
                'description' => null,
                'primary_cta_label' => null,
                'primary_cta_url' => null,
                'members' => [],
            ],
            'seo' => [
                'meta_title' => null,
                'meta_description' => null,
            ],
        ];
    }
}
