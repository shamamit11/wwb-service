<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Modules\Seo\Services\CanonicalUrlService;
use Illuminate\Http\Request;

class PageResource extends ApiResource
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
            'ulid' => $this->resource->ulid,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'type' => $this->resource->type,
            'status' => $this->resource->status,
            'summary' => $this->resource->summary,
            'content_markdown' => $this->resource->content_markdown,
            'visibility' => $this->resource->visibility,
            'published_at' => $this->resource->published_at?->toISOString(),
            'scheduled_for' => $this->resource->scheduled_for?->toISOString(),
            'canonical_url' => $canonicalUrls->for($this->resource),
            'meta' => $this->resource->meta ?? [],
            'created_by' => $this->whenLoaded('createdBy', fn (): array => [
                'id' => $this->resource->createdBy->id,
                'name' => $this->resource->createdBy->name,
                'email' => $this->resource->createdBy->email,
            ]),
            'updated_by' => $this->whenLoaded('updatedBy', fn (): ?array => $this->resource->updatedBy === null ? null : [
                'id' => $this->resource->updatedBy->id,
                'name' => $this->resource->updatedBy->name,
                'email' => $this->resource->updatedBy->email,
            ]),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
