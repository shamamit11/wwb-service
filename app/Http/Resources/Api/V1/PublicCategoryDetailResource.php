<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class PublicCategoryDetailResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['category']->id,
            'name' => $this->resource['category']->name,
            'slug' => $this->resource['category']->slug,
            'description' => $this->resource['category']->description,
            'post_count' => (int) ($this->resource['category']->published_posts_count ?? 0),
            'seo' => $this->resource['category']->relationLoaded('seo') && $this->resource['category']->seo !== null
                ? (new PublicSeoMetadataResource($this->resource['category']->seo->loadMissing('ogImageMedia')))->resolve()
                : null,
            'posts' => PublicPostSummaryResource::collection($this->resource['posts'])->resolve(),
        ];
    }
}
