<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Modules\Seo\Services\CanonicalUrlService;
use Illuminate\Http\Request;

class RssFeedEntryResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CanonicalUrlService $canonicalUrls */
        $canonicalUrls = app(CanonicalUrlService::class);

        return [
            'type' => 'post',
            'id' => $this->resource->id,
            'slug' => $this->resource->slug,
            'title' => $this->resource->title,
            'description' => $this->resource->seo?->meta_description ?? $this->resource->short_description,
            'link' => $canonicalUrls->for($this->resource),
            'published_at' => $this->resource->published_at?->toISOString(),
            'last_modified_at' => $this->resource->updated_at?->toISOString(),
            'author' => $this->whenLoaded('author', fn (): array => [
                'id' => $this->resource->author->id,
                'name' => $this->resource->author->name,
            ]),
            'category' => $this->whenLoaded('category', fn (): ?array => $this->resource->category === null ? null : [
                'id' => $this->resource->category->id,
                'name' => $this->resource->category->name,
                'slug' => $this->resource->category->slug,
            ]),
        ];
    }
}
