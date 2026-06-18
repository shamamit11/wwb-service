<?php

namespace App\Modules\Ai\Repositories;

use App\Models\AiJob;
use App\Models\AiJobCost;
use App\Modules\Ai\Data\CreateAiJobCostData;
use Illuminate\Database\Eloquent\Collection;

class EloquentAiJobCostRepository implements AiJobCostRepository
{
    public function create(CreateAiJobCostData $data): AiJobCost
    {
        return AiJobCost::query()->create([
            'ai_job_id' => $data->aiJobId,
            'ai_generation_step_id' => $data->aiGenerationStepId,
            'provider' => $data->provider,
            'model' => $data->model,
            'input_tokens' => $data->inputTokens,
            'output_tokens' => $data->outputTokens,
            'total_tokens' => $data->totalTokens,
            'estimated_cost' => $data->estimatedCost,
            'actual_cost' => $data->actualCost,
            'currency' => $data->currency,
            'metadata' => $data->metadata,
        ])->refresh();
    }

    public function updateJobAggregate(AiJob $job, CreateAiJobCostData $data): AiJobCost
    {
        AiJobCost::query()->updateOrCreate(
            [
                'ai_job_id' => $job->id,
                'ai_generation_step_id' => null,
            ],
            [
                'provider' => $data->provider,
                'model' => $data->model,
                'input_tokens' => $data->inputTokens,
                'output_tokens' => $data->outputTokens,
                'total_tokens' => $data->totalTokens,
                'estimated_cost' => $data->estimatedCost,
                'actual_cost' => $data->actualCost,
                'currency' => $data->currency,
                'metadata' => $data->metadata,
            ],
        );

        return AiJobCost::query()
            ->where('ai_job_id', $job->id)
            ->whereNull('ai_generation_step_id')
            ->firstOrFail();
    }

    /**
     * @return Collection<int, AiJobCost>
     */
    public function findByJob(AiJob $job): Collection
    {
        return AiJobCost::query()
            ->where('ai_job_id', $job->id)
            ->orderBy('id')
            ->get();
    }
}
