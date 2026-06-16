<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'seoable_type',
    'seoable_id',
    'meta_title',
    'meta_description',
    'canonical_url',
    'robots_index',
    'robots_follow',
    'og_title',
    'og_description',
    'og_image_media_id',
    'schema_type',
    'schema_payload',
    'focus_keyword',
])]
class SeoMetadata extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'schema_payload' => 'array',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function ogImageMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_media_id')->withTrashed();
    }
}
