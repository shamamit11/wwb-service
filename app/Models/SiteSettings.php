<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'singleton_key',
    'footer',
    'updated_by_user_id',
])]
class SiteSettings extends Model
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
            'footer' => 'array',
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
            'footer' => [
                'brand_name' => null,
                'description' => null,
                'social_links' => [],
                'legal_links' => [],
            ],
        ];
    }
}
