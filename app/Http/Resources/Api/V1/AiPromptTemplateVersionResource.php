<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class AiPromptTemplateVersionResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'prompt_template_id' => $this->resource->prompt_template_id,
            'version' => $this->resource->version,
            'system_prompt' => $this->resource->system_prompt,
            'user_prompt' => $this->resource->user_prompt,
            'output_schema' => $this->resource->output_schema,
            'variables' => $this->resource->variables ?? [],
            'status' => $this->resource->status,
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
