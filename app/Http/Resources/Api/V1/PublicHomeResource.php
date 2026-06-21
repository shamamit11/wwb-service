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
            'hero' => $this->resource['hero'],
            'featured_editorial' => [
                ...$this->resource['featured_editorial'],
                'posts' => PublicPostSummaryResource::collection($this->resource['featured_editorial']['posts'])->resolve(),
            ],
            'guide_section' => [
                ...$this->resource['guide_section'],
                'posts' => PublicPostSummaryResource::collection($this->resource['guide_section']['posts'])->resolve(),
            ],
            'topic_section' => [
                ...$this->resource['topic_section'],
                'categories' => PublicCategorySummaryResource::collection($this->resource['topic_section']['categories'])->resolve(),
            ],
            'promo_section' => $this->resource['promo_section'],
            'newsletter_section' => $this->resource['newsletter_section'],
            'seo' => $this->resource['seo'],
        ];
    }
}
