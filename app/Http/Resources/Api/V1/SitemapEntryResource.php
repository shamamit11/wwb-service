<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Modules\Seo\Services\CanonicalUrlService;
use Illuminate\Http\Request;

class SitemapEntryResource extends ApiResource
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
            'canonical_url' => $canonicalUrls->for($this->resource),
            'published_at' => $this->resource->published_at?->toISOString(),
            'last_modified_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
