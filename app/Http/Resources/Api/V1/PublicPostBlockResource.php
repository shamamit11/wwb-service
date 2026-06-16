<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class PublicPostBlockResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'block_type' => $this->resource->block_type,
            'sort_order' => $this->resource->sort_order,
            'content_markdown' => $this->resource->content_markdown,
            'settings' => $this->resource->settings ?? [],
        ];
    }
}
