<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'singleton_key',
    'hero',
    'contact_form',
    'contact_reasons',
    'seo',
    'updated_by_user_id',
])]
class ContactPage extends Model
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
            'contact_form' => 'array',
            'contact_reasons' => 'array',
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
            ],
            'contact_form' => [
                'eyebrow' => null,
                'title' => null,
                'description' => null,
                'submit_label' => null,
                'success_message' => null,
            ],
            'contact_reasons' => [
                'items' => [],
            ],
            'seo' => [
                'meta_title' => null,
                'meta_description' => null,
            ],
        ];
    }
}
