<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'type',
    'status',
    'entity_type',
    'entity_id',
    'provider',
    'model',
    'input_payload',
    'output_payload',
    'usage_payload',
    'error_message',
    'attempts',
    'retry_of_ai_job_id',
    'started_at',
    'completed_at',
    'failed_at',
])]
class AiJob extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_QUEUED,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED,
        self::STATUS_REVIEWED,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entity_id' => 'integer',
            'input_payload' => 'array',
            'output_payload' => 'array',
            'usage_payload' => 'array',
            'attempts' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<AiGenerationStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(AiGenerationStep::class)
            ->orderBy('id');
    }

    /**
     * @return BelongsTo<AiJob, $this>
     */
    public function retryOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'retry_of_ai_job_id');
    }

    /**
     * @return HasMany<AiJob, $this>
     */
    public function retries(): HasMany
    {
        return $this->hasMany(self::class, 'retry_of_ai_job_id')
            ->orderByDesc('id');
    }

    /**
     * @return HasMany<Media, $this>
     */
    public function generatedMedia(): HasMany
    {
        return $this->hasMany(Media::class, 'generated_by_ai_job_id');
    }

    public function canRetry(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
