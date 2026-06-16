<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class PublicCategorySummaryResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'post_count' => (int) ($this->resource->published_posts_count ?? 0),
            'seo' => $this->whenLoaded('seo', fn (): array => (new PublicSeoMetadataResource($this->resource->seo->loadMissing('ogImageMedia')))->resolve()),
        ];
    }
}
