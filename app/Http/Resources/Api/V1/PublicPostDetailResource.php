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
        $contentMarkdown = $this->contentMarkdown();
        $relatedPosts = $this->resource->relationLoaded('relatedPosts')
            ? $this->resource->getRelation('relatedPosts')
            : collect();

        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'excerpt' => $this->resource->excerpt,
            'canonical_url' => $canonicalUrls->for($this->resource),
            'published_at' => $this->resource->published_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'reading_time_minutes' => $this->resource->reading_time_minutes,
            'read_time' => $this->formatReadTime($this->resource->reading_time_minutes),
            'word_count' => $this->resource->word_count,
            'content' => $contentMarkdown,
            'content_markdown' => $contentMarkdown,
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
            'template' => $this->resource->template !== null && $this->resource->template->status === Template::STATUS_ACTIVE
                ? (new PublicTemplateResource($this->resource->template))->resolve()
                : null,
            'seo' => $this->resource->seo === null ? null : (new PublicSeoMetadataResource($this->resource->seo->loadMissing('ogImageMedia')))->resolve(),
            'related_posts' => PublicPostSummaryResource::collection($relatedPosts)->resolve(),
            'schema' => $schemas->handle('post', $this->resource->id),
            'blocks' => PublicPostBlockResource::collection($this->resource->blocks)->resolve(),
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

    private function contentMarkdown(): ?string
    {
        $parts = $this->resource->blocks
            ->map(fn ($block): ?string => filled($block->content_markdown) ? trim((string) $block->content_markdown) : null)
            ->filter(fn (?string $content): bool => $content !== null && $content !== '')
            ->values()
            ->all();

        if ($parts === []) {
            return null;
        }

        return implode("\n\n", $parts);
    }
}
