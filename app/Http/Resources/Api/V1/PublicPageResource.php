<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Modules\Seo\Services\CanonicalUrlService;
use Illuminate\Http\Request;

class PublicPageResource extends ApiResource
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
            'type' => $this->resource->type,
            'summary' => $this->resource->summary,
            'content_markdown' => $this->resource->content_markdown,
            'published_at' => $this->resource->published_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'canonical_url' => $canonicalUrls->for($this->resource),
            'meta' => $this->resource->meta ?? [],
            'seo' => $this->resource->seo === null ? null : (new PublicSeoMetadataResource($this->resource->seo->loadMissing('ogImageMedia')))->resolve(),
        ];
    }
}
