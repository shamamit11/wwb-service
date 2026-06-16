<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class PublicSeoMetadataResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'meta_title' => $this->resource->meta_title,
            'meta_description' => $this->resource->meta_description,
            'canonical_url' => $this->resource->canonical_url,
            'robots_index' => (bool) $this->resource->robots_index,
            'robots_follow' => (bool) $this->resource->robots_follow,
            'og_title' => $this->resource->og_title,
            'og_description' => $this->resource->og_description,
            'og_image' => $this->whenLoaded('ogImageMedia', fn (): ?array => $this->resource->ogImageMedia === null ? null : (new PublicMediaResource($this->resource->ogImageMedia))->resolve()),
            'schema_type' => $this->resource->schema_type,
        ];
    }
}
