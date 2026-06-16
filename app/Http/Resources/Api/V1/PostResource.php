<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Modules\Media\Services\Contracts\MediaReader;
use App\Modules\Seo\Services\CanonicalUrlService;
use Illuminate\Http\Request;

class PostResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var MediaReader $reader */
        $reader = app(MediaReader::class);
        /** @var CanonicalUrlService $canonicalUrls */
        $canonicalUrls = app(CanonicalUrlService::class);

        return [
            'id' => $this->resource->id,
            'ulid' => $this->resource->ulid,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'excerpt' => $this->resource->excerpt,
            'status' => $this->resource->status,
            'visibility' => $this->resource->visibility,
            'published_at' => $this->resource->published_at?->toISOString(),
            'scheduled_for' => $this->resource->scheduled_for?->toISOString(),
            'canonical_url' => $canonicalUrls->for($this->resource),
            'content_version' => $this->resource->content_version,
            'reading_time_minutes' => $this->resource->reading_time_minutes,
            'word_count' => $this->resource->word_count,
            'is_featured' => (bool) $this->resource->is_featured,
            'meta' => $this->resource->meta ?? [],
            'author' => $this->whenLoaded('author', fn (): array => [
                'id' => $this->resource->author->id,
                'name' => $this->resource->author->name,
                'email' => $this->resource->author->email,
            ]),
            'category' => $this->whenLoaded('category', fn (): array => [
                'id' => $this->resource->category->id,
                'name' => $this->resource->category->name,
                'slug' => $this->resource->category->slug,
            ]),
            'template' => $this->whenLoaded('template', fn (): ?array => $this->resource->template === null ? null : [
                'id' => $this->resource->template->id,
                'name' => $this->resource->template->name,
                'slug' => $this->resource->template->slug,
                'template_type' => $this->resource->template->template_type,
            ]),
            'featured_media' => $this->whenLoaded('featuredMedia', fn (): ?array => $this->resource->featuredMedia === null ? null : [
                'id' => $this->resource->featuredMedia->id,
                'ulid' => $this->resource->featuredMedia->ulid,
                'original_filename' => $this->resource->featuredMedia->original_filename,
                'mime_type' => $this->resource->featuredMedia->mime_type,
                'alt_text' => $this->resource->featuredMedia->alt_text,
                'caption' => $this->resource->featuredMedia->caption,
                'url' => $reader->url($this->resource->featuredMedia),
            ]),
            'tags' => $this->whenLoaded('tags', fn () => $this->resource->tags
                ->map(fn ($tag): array => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])
                ->values()
                ->all()),
            'blocks' => PostBlockResource::collection($this->whenLoaded('blocks')),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
