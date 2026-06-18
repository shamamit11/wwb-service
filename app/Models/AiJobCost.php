<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ai_job_id',
    'ai_generation_step_id',
    'provider',
    'model',
    'input_tokens',
    'output_tokens',
    'total_tokens',
    'estimated_cost',
    'actual_cost',
    'currency',
    'metadata',
])]
class AiJobCost extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'total_tokens' => 'integer',
            'estimated_cost' => 'decimal:8',
            'actual_cost' => 'decimal:8',
            'metadata' => 'array',
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
     * @return BelongsTo<AiGenerationStep, $this>
     */
    public function aiGenerationStep(): BelongsTo
    {
        return $this->belongsTo(AiGenerationStep::class);
    }
}
