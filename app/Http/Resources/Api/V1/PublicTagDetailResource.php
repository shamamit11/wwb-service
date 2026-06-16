<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class PublicTagDetailResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['tag']->id,
            'name' => $this->resource['tag']->name,
            'slug' => $this->resource['tag']->slug,
            'description' => $this->resource['tag']->description,
            'post_count' => (int) ($this->resource['tag']->published_posts_count ?? 0),
            'posts' => PublicPostSummaryResource::collection($this->resource['posts'])->resolve(),
        ];
    }
}
