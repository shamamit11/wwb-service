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
            'wordCount' => $this->wordCount($post->full_article_markdown),
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

        return $this->mergeOverrides($schema, $metadata?->schema_payload);
    }

    /**
     * @param  array<string, mixed>|null  $overrides
     * @return array<string, mixed>
     */
    private function mergeOverrides(array $schema, ?array $overrides): array
    {
        if ($overrides === null || $overrides === []) {
            return $schema;
        }

        return array_replace_recursive($schema, $overrides);
    }

    private function wordCount(?string $markdown): int
    {
        if ($markdown === null || trim($markdown) === '') {
            return 0;
        }

        preg_match_all('/\pL[\pL\pN\'_-]*/u', strip_tags($markdown), $matches);

        return count($matches[0]);
    }
}
