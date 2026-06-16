<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'ulid',
    'uploaded_by_user_id',
    'generated_by_ai_job_id',
    'storage_provider',
    'bucket_name',
    'object_key',
    'original_filename',
    'mime_type',
    'extension',
    'file_size_bytes',
    'checksum_sha256',
    'width',
    'height',
    'alt_text',
    'caption',
    'source_type',
    'source_url',
    'attribution_text',
    'status',
    'metadata',
])]
class Media extends Model
{
    use HasUlids, SoftDeletes;

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
            'file_size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function featuredOnPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'featured_media_id');
    }
}
