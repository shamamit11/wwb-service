<?php

namespace App\Modules\Ai\Repositories;

use App\Models\AiJob;
use App\Modules\Ai\Data\AiJobFiltersData;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Data\UpdateAiJobStatusData;
use Illuminate\Database\Eloquent\Collection;

class EloquentAiJobRepository implements AiJobRepository
{
    public function create(CreateAiJobData $data): AiJob
    {
        return AiJob::query()->create([
            'type' => $data->type,
            'status' => $data->status,
            'entity_type' => $data->entityType,
            'entity_id' => $data->entityId,
            'provider' => $data->provider,
            'model' => $data->model,
            'input_payload' => $data->inputPayload,
            'output_payload' => $data->outputPayload,
            'usage_payload' => $data->usagePayload,
            'error_message' => $data->errorMessage,
            'attempts' => $data->attempts,
            'retry_of_ai_job_id' => $data->retryOfAiJobId,
            'started_at' => $data->startedAt,
            'completed_at' => $data->completedAt,
            'failed_at' => $data->failedAt,
        ])->refresh()->loadCount('steps');
    }

    public function updateStatus(AiJob $job, UpdateAiJobStatusData $data): AiJob
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

        $job->update(array_filter(
            $attributes,
            static fn ($value, string $key): bool => $key === 'status' || $value !== null,
            ARRAY_FILTER_USE_BOTH,
        ));

        return $this->refresh($job);
    }

    public function findById(int $id): ?AiJob
    {
        return AiJob::query()
            ->with(['costs', 'steps.costs', 'retryOf', 'retries'])
            ->withCount('steps')
            ->find($id);
    }

    /**
     * @return Collection<int, AiJob>
     */
    public function search(AiJobFiltersData $filters): Collection
    {
        [$sortColumn, $descending] = $this->normalizeSort($filters->sort);

        return AiJob::query()
            ->with(['costs', 'retryOf'])
            ->withCount('steps')
            ->when($filters->status, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters->type, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters->entityType, fn ($query, string $entityType) => $query->where('entity_type', $entityType))
            ->when($filters->entityId, fn ($query, int $entityId) => $query->where('entity_id', $entityId))
            ->when($filters->provider, fn ($query, string $provider) => $query->where('provider', $provider))
            ->when($filters->model, fn ($query, string $model) => $query->where('model', $model))
            ->orderBy($sortColumn, $descending ? 'desc' : 'asc')
            ->orderByDesc('id')
            ->get();
    }

    private function refresh(AiJob $job): AiJob
    {
        return $job->refresh()->load(['costs', 'steps.costs', 'retryOf', 'retries'])->loadCount('steps');
    }

    /**
     * @return array{0: string, 1: bool}
     */
    private function normalizeSort(string $sort): array
    {
        $descending = str_starts_with($sort, '-');
        $field = ltrim($sort, '-');
        $allowed = ['created_at', 'updated_at', 'started_at', 'completed_at', 'failed_at', 'attempts'];

        if (! in_array($field, $allowed, true)) {
            return ['created_at', true];
        }

        return [$field, $descending];
    }
}
