<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class TemplateResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'ulid' => $this->resource->ulid,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'template_type' => $this->resource->template_type,
            'description' => $this->resource->description,
            'status' => $this->resource->status,
            'default_excerpt_prompt' => $this->resource->default_excerpt_prompt,
            'default_meta' => $this->resource->default_meta ?? [],
            'blocks' => TemplateBlockResource::collection($this->whenLoaded('blocks')),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
