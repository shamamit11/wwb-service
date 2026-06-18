<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class AiJobCostResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'ai_job_id' => $this->resource->ai_job_id,
            'ai_generation_step_id' => $this->resource->ai_generation_step_id,
            'provider' => $this->resource->provider,
            'model' => $this->resource->model,
            'input_tokens' => $this->resource->input_tokens,
            'output_tokens' => $this->resource->output_tokens,
            'total_tokens' => $this->resource->total_tokens,
            'estimated_cost' => $this->resource->estimated_cost,
            'actual_cost' => $this->resource->actual_cost,
            'currency' => $this->resource->currency,
            'metadata' => $this->resource->metadata,
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
