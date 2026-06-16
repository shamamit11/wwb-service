<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class PublicHomeResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'featured_posts' => PublicPostSummaryResource::collection($this->resource['featured_posts'])->resolve(),
            'latest_posts' => PublicPostSummaryResource::collection($this->resource['latest_posts'])->resolve(),
            'categories' => PublicCategorySummaryResource::collection($this->resource['categories'])->resolve(),
            'seo' => $this->resource['seo'],
        ];
    }
}
