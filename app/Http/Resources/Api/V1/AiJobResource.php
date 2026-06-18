<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class AiJobResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'type' => $this->resource->type,
            'status' => $this->resource->status,
            'entity_type' => $this->resource->entity_type,
            'entity_id' => $this->resource->entity_id,
            'provider' => $this->resource->provider,
            'model' => $this->resource->model,
            'input_payload' => $this->resource->input_payload,
            'output_payload' => $this->resource->output_payload,
            'usage_payload' => $this->resource->usage_payload,
            'error_message' => $this->resource->error_message,
            'attempts' => $this->resource->attempts,
            'retry_of_ai_job_id' => $this->resource->retry_of_ai_job_id,
            'can_retry' => $this->resource->canRetry(),
            'steps_count' => $this->whenCounted('steps'),
            'started_at' => $this->resource->started_at?->toISOString(),
            'completed_at' => $this->resource->completed_at?->toISOString(),
            'failed_at' => $this->resource->failed_at?->toISOString(),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'retry_of' => $this->whenLoaded('retryOf', fn (): ?array => $this->resource->retryOf ? [
                'id' => $this->resource->retryOf->id,
                'status' => $this->resource->retryOf->status,
                'type' => $this->resource->retryOf->type,
            ] : null),
            'retries' => AiJobResource::collection($this->whenLoaded('retries')),
            'steps' => AiGenerationStepResource::collection($this->whenLoaded('steps')),
        ];
    }
}
