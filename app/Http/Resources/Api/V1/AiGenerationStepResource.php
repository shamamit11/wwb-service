<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class AiGenerationStepResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'ai_job_id' => $this->resource->ai_job_id,
            'agent_name' => $this->resource->agent_name,
            'status' => $this->resource->status,
            'input_payload' => $this->resource->input_payload,
            'output_payload' => $this->resource->output_payload,
            'usage_payload' => $this->resource->usage_payload,
            'error_message' => $this->resource->error_message,
            'costs' => AiJobCostResource::collection($this->whenLoaded('costs')),
            'started_at' => $this->resource->started_at?->toISOString(),
            'completed_at' => $this->resource->completed_at?->toISOString(),
            'failed_at' => $this->resource->failed_at?->toISOString(),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
