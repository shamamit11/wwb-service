<?php

namespace App\Modules\Ai\Repositories;

use App\Models\AiGenerationStep;
use App\Modules\Ai\Data\CreateAiGenerationStepData;
use App\Modules\Ai\Data\UpdateAiGenerationStepStatusData;

class EloquentAiGenerationStepRepository implements AiGenerationStepRepository
{
    public function create(CreateAiGenerationStepData $data): AiGenerationStep
    {
        return AiGenerationStep::query()->create([
            'ai_job_id' => $data->aiJobId,
            'agent_name' => $data->agentName,
            'status' => $data->status,
            'input_payload' => $data->inputPayload,
            'output_payload' => $data->outputPayload,
            'usage_payload' => $data->usagePayload,
            'error_message' => $data->errorMessage,
            'started_at' => $data->startedAt,
            'completed_at' => $data->completedAt,
            'failed_at' => $data->failedAt,
        ])->refresh();
    }

    public function updateStatus(AiGenerationStep $step, UpdateAiGenerationStepStatusData $data): AiGenerationStep
    {
        $attributes = [
            'status' => $data->status,
            'output_payload' => $data->outputPayload,
            'usage_payload' => $data->usagePayload,
            'error_message' => $data->errorMessage,
            'started_at' => $data->startedAt,
            'completed_at' => $data->completedAt,
            'failed_at' => $data->failedAt,
        ];

        $step->update(array_filter(
            $attributes,
            static fn ($value, string $key): bool => $key === 'status' || $value !== null,
            ARRAY_FILTER_USE_BOTH,
        ));

        return $step->refresh();
    }
}
