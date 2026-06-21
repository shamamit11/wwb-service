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
            'short_description' => $this->resource->short_description,
            'canonical_url' => $canonicalUrls->for($this->resource),
            'published_at' => $this->resource->published_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'reading_time_minutes' => $this->readingTimeMinutes(),
            'read_time' => $this->formatReadTime($this->readingTimeMinutes()),
            'featured_image' => $this->featuredImageUrl(),
            'featured_media' => $this->whenLoaded('featuredMedia', fn (): ?array => $this->resource->featuredMedia === null ? null : (new PublicMediaResource($this->resource->featuredMedia))->resolve()),
            'author' => $this->whenLoaded('author', fn (): ?array => $this->resource->author === null ? null : [
                'id' => $this->resource->author->id,
                'name' => $this->resource->author->name,
            ]),
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

    private function formatReadTime(?int $minutes): ?string
    {
        if ($minutes === null || $minutes <= 0) {
            return null;
        }

        return "{$minutes} min read";
    }

    private function featuredImageUrl(): ?string
    {
        if (! $this->resource->relationLoaded('featuredMedia') || $this->resource->featuredMedia === null) {
            return null;
        }

        return (new PublicMediaResource($this->resource->featuredMedia))->resolve()['url'] ?? null;
    }

    private function readingTimeMinutes(): ?int
    {
        $markdown = trim((string) ($this->resource->full_article_markdown ?? ''));

        if ($markdown === '') {
            return null;
        }

        preg_match_all('/\pL[\pL\pN\'_-]*/u', strip_tags($markdown), $matches);

        return max(1, (int) ceil(count($matches[0]) / 200));
    }
}
