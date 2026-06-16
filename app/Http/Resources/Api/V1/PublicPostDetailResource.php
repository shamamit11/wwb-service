<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Models\Template;
use App\Modules\Seo\Services\CanonicalUrlService;
use App\Modules\Seo\Services\GenerateSchemaPayloadService;
use Illuminate\Http\Request;

class PublicPostDetailResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CanonicalUrlService $canonicalUrls */
        $canonicalUrls = app(CanonicalUrlService::class);
        /** @var GenerateSchemaPayloadService $schemas */
        $schemas = app(GenerateSchemaPayloadService::class);

        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'excerpt' => $this->resource->excerpt,
            'canonical_url' => $canonicalUrls->for($this->resource),
            'published_at' => $this->resource->published_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'featured_media' => $this->resource->featuredMedia === null ? null : (new PublicMediaResource($this->resource->featuredMedia))->resolve(),
            'category' => $this->resource->category === null ? null : [
                'id' => $this->resource->category->id,
                'name' => $this->resource->category->name,
                'slug' => $this->resource->category->slug,
            ],
            'tags' => $this->resource->tags->map(fn ($tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->values()->all(),
            'template' => $this->resource->template !== null && $this->resource->template->status === Template::STATUS_ACTIVE
                ? (new PublicTemplateResource($this->resource->template))->resolve()
                : null,
            'seo' => $this->resource->seo === null ? null : (new PublicSeoMetadataResource($this->resource->seo->loadMissing('ogImageMedia')))->resolve(),
            'schema' => $schemas->handle('post', $this->resource->id),
            'blocks' => PublicPostBlockResource::collection($this->resource->blocks)->resolve(),
        ];
    }
}
