<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'ai_job_id',
    'agent_name',
    'status',
    'input_payload',
    'output_payload',
    'usage_payload',
    'error_message',
    'started_at',
    'completed_at',
    'failed_at',
])]
class AiGenerationStep extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input_payload' => 'array',
            'output_payload' => 'array',
            'usage_payload' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<AiJob, $this>
     */
    public function aiJob(): BelongsTo
    {
        return $this->belongsTo(AiJob::class);
    }

    /**
     * @return HasMany<AiJobCost, $this>
     */
    public function costs(): HasMany
    {
        return $this->hasMany(AiJobCost::class)
            ->orderBy('id');
    }
}
