<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class TemplateBlockResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'block_key' => $this->resource->block_key,
            'block_type' => $this->resource->block_type,
            'sort_order' => $this->resource->sort_order,
            'label' => $this->resource->label,
            'default_markdown' => $this->resource->default_markdown,
            'settings' => $this->resource->settings ?? [],
            'is_required' => (bool) $this->resource->is_required,
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
