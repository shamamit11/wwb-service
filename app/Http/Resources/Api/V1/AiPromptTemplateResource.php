<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class AiPromptTemplateResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'key' => $this->resource->key,
            'type' => $this->resource->type,
            'description' => $this->resource->description,
            'status' => $this->resource->status,
            'active_version_id' => $this->resource->active_version_id,
            'versions_count' => $this->whenCounted('versions'),
            'active_version' => $this->whenLoaded('activeVersion', fn () => $this->resource->activeVersion
                ? new AiPromptTemplateVersionResource($this->resource->activeVersion)
                : null),
            'versions' => AiPromptTemplateVersionResource::collection($this->whenLoaded('versions')),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
