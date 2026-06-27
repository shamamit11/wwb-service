<?php

namespace App\Modules\Seo\Services;

use App\Models\Category;
use App\Models\KnowledgeBaseEntry;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;

class CanonicalUrlService
{
    public function for(Model $seoable): ?string
    {
        $override = trim((string) ($seoable->seo?->canonical_url ?? ''));

        if ($override !== '') {
            return $this->normalizeFor($override, $seoable);
        }

        return match ($seoable::class) {
            Post::class => $this->forPost($seoable),
            Category::class => $this->forCategory($seoable),
            Page::class => $this->forPage($seoable),
            KnowledgeBaseEntry::class => $this->forKnowledgeBaseEntry($seoable),
            default => null,
        };
    }

    private function forPost(Post $post): ?string
    {
        if ($post->status !== Post::STATUS_PUBLISHED || $post->visibility !== Post::VISIBILITY_PUBLIC) {
            return null;
        }

        return $this->absolute("articles/{$post->slug}/");
    }

    private function forCategory(Category $category): ?string
    {
        if (! $category->is_active) {
            return null;
        }

        return $this->absolute("categories/{$category->slug}/");
    }

    private function forKnowledgeBaseEntry(KnowledgeBaseEntry $entry): ?string
    {
        if ($entry->status !== KnowledgeBaseEntry::STATUS_ACTIVE) {
            return null;
        }

        return $this->absolute("knowledge-base/{$entry->slug}/");
    }

    private function forPage(Page $page): ?string
    {
        if ($page->status !== Page::STATUS_PUBLISHED || $page->visibility !== Page::VISIBILITY_PUBLIC) {
            return null;
        }

        return $this->absolute("pages/{$page->slug}/");
    }

    public function normalize(?string $url): ?string
    {
        $trimmed = trim((string) $url);

        if ($trimmed === '') {
            return null;
        }

        $serviceUrl = rtrim((string) config('app.url'), '/');
        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        if ($serviceUrl === '' || $frontendUrl === '' || $serviceUrl === $frontendUrl) {
            return $trimmed;
        }

        if (! str_starts_with($trimmed, $serviceUrl)) {
            return $trimmed;
        }

        $remainder = substr($trimmed, strlen($serviceUrl));

        if ($remainder !== '' && ! str_starts_with($remainder, '/')) {
            return $trimmed;
        }

        return $frontendUrl.$remainder;
    }

    public function normalizeFor(?string $url, ?Model $seoable = null): ?string
    {
        $normalized = $this->normalize($url);

        if ($normalized === null) {
            return null;
        }

        return $seoable instanceof Post
            ? $this->normalizeLegacyPostPath($normalized)
            : $normalized;
    }

    private function normalizeLegacyPostPath(string $url): string
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');
        $frontendHost = parse_url($frontendUrl, PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);

        if (! is_string($frontendHost) || $frontendHost === '' || ! is_string($urlHost) || $urlHost !== $frontendHost) {
            return $url;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || $path === '' || $path === '/') {
            return $url;
        }

        $trimmed = trim($path, '/');

        if ($trimmed === '' || str_contains($trimmed, '/')) {
            return $url;
        }

        $query = parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);
        $normalized = $frontendUrl."/articles/{$trimmed}/";

        if (is_string($query) && $query !== '') {
            $normalized .= '?'.$query;
        }

        if (is_string($fragment) && $fragment !== '') {
            $normalized .= '#'.$fragment;
        }

        return $normalized;
    }

    private function absolute(string $path): string
    {
        return rtrim((string) config('app.frontend_url', config('app.url')), '/').'/'.ltrim($path, '/');
    }
}
