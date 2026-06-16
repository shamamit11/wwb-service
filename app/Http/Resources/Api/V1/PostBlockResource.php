<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class PostBlockResource extends ApiResource
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
            'content_markdown' => $this->resource->content_markdown,
            'content_html_cache' => $this->resource->content_html_cache,
            'plain_text_cache' => $this->resource->plain_text_cache,
            'settings' => $this->resource->settings ?? [],
            'source_template_block_id' => $this->resource->source_template_block_id,
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
