<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Modules\Seo\Services\CanonicalUrlService;
use Illuminate\Http\Request;

class PublicPostSummaryResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CanonicalUrlService $canonicalUrls */
        $canonicalUrls = app(CanonicalUrlService::class);

        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'excerpt' => $this->resource->excerpt,
            'canonical_url' => $canonicalUrls->for($this->resource),
            'published_at' => $this->resource->published_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'featured_media' => $this->whenLoaded('featuredMedia', fn (): ?array => $this->resource->featuredMedia === null ? null : (new PublicMediaResource($this->resource->featuredMedia))->resolve()),
            'category' => $this->whenLoaded('category', fn (): array => [
                'id' => $this->resource->category->id,
                'name' => $this->resource->category->name,
                'slug' => $this->resource->category->slug,
            ]),
            'tags' => $this->whenLoaded('tags', fn () => $this->resource->tags->map(fn ($tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->values()->all()),
            'seo' => $this->whenLoaded('seo', fn (): array => (new PublicSeoMetadataResource($this->resource->seo->loadMissing('ogImageMedia')))->resolve()),
        ];
    }
}
