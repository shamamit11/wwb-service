<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
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
        $contentHtml = $this->contentHtml();
        $relatedPosts = $this->resource->relationLoaded('relatedPosts')
            ? $this->resource->getRelation('relatedPosts')
            : collect();

        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'short_description' => $this->resource->short_description,
            'description' => $this->resource->description,
            'canonical_url' => $canonicalUrls->for($this->resource),
            'published_at' => $this->resource->published_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'reading_time_minutes' => $this->readingTimeMinutes($contentHtml),
            'read_time' => $this->formatReadTime($this->readingTimeMinutes($contentHtml)),
            'word_count' => $this->wordCount($contentHtml),
            'content' => $contentHtml,
            'full_article_html' => $this->resource->full_article_html,
            'full_article_delta' => $this->resource->full_article_delta,
            'faq' => $this->resource->faq ?? [],
            'author' => $this->resource->author === null ? null : [
                'id' => $this->resource->author->id,
                'name' => $this->resource->author->name,
            ],
            'featured_image' => $this->featuredImageUrl(),
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
            'seo' => $this->resource->seo === null ? null : (new PublicSeoMetadataResource($this->resource->seo->loadMissing('ogImageMedia')))->resolve(),
            'related_posts' => PublicPostSummaryResource::collection($relatedPosts)->resolve(),
            'schema' => $schemas->handle('post', $this->resource->id),
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
        if ($this->resource->featuredMedia === null) {
            return null;
        }

        return (new PublicMediaResource($this->resource->featuredMedia))->resolve()['url'] ?? null;
    }

    private function contentHtml(): ?string
    {
        if (filled($this->resource->full_article_html)) {
            return trim((string) $this->resource->full_article_html);
        }

        return null;
    }

    private function readingTimeMinutes(?string $html): ?int
    {
        $wordCount = $this->wordCount($html);

        if ($wordCount <= 0) {
            return null;
        }

        return max(1, (int) ceil($wordCount / 200));
    }

    private function wordCount(?string $html): int
    {
        if ($html === null || trim($html) === '') {
            return 0;
        }

        preg_match_all('/\pL[\pL\pN\'_-]*/u', strip_tags($html), $matches);

        return count($matches[0]);
    }
}
