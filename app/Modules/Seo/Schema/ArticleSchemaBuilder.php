<?php

namespace App\Modules\Seo\Schema;

use App\Models\Post;
use App\Modules\Media\Services\Contracts\MediaReader;
use App\Modules\Seo\Services\CanonicalUrlService;

class ArticleSchemaBuilder
{
    public function __construct(
        private readonly CanonicalUrlService $canonicalUrls,
        private readonly MediaReader $media,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Post $post): array
    {
        $baseUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');
        $canonical = $this->canonicalUrls->for($post) ?? "{$baseUrl}/";
        $metadata = $post->seo;

        $schema = [
            '@type' => $metadata?->schema_type ?: 'Article',
            '@id' => "{$canonical}#article",
            'headline' => $metadata?->meta_title ?: $post->title,
            'description' => $metadata?->meta_description ?: $post->short_description ?: $post->description,
            'url' => $canonical,
            'mainEntityOfPage' => $canonical,
            'datePublished' => $post->published_at?->toISOString(),
            'dateModified' => $post->updated_at?->toISOString(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author?->name,
            ],
            'publisher' => [
                '@id' => "{$baseUrl}/#organization",
            ],
            'articleSection' => $post->category?->name,
            'keywords' => $metadata?->focus_keyword ?: ($post->tags->pluck('name')->implode(', ') ?: null),
            'wordCount' => $this->wordCount($post->full_article_html),
            'isAccessibleForFree' => true,
            'breadcrumb' => [
                '@id' => "{$canonical}#breadcrumb",
            ],
        ];

        if ($metadata?->ogImageMedia !== null) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => $this->media->url($metadata->ogImageMedia),
            ];
        }

        return $this->mergeOverrides($schema, $metadata?->schema_payload, $post);
    }

    /**
     * @param  array<string, mixed>|null  $overrides
     * @return array<string, mixed>
     */
    private function mergeOverrides(array $schema, ?array $overrides, Post $post): array
    {
        if ($overrides === null || $overrides === []) {
            return $schema;
        }

        return $this->normalizeUrls(
            array_replace_recursive($schema, $overrides),
            $post,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizeUrls(array $payload, Post $post): array
    {
        foreach ($payload as $key => $value) {
            if (is_string($value)) {
                $payload[$key] = $this->canonicalUrls->normalizeFor($value, $post) ?? $value;

                continue;
            }

            if (is_array($value)) {
                $payload[$key] = $this->normalizeUrls($value, $post);
            }
        }

        return $payload;
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
