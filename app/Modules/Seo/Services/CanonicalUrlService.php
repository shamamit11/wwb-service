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
            return $this->normalize($override);
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

    private function absolute(string $path): string
    {
        return rtrim((string) config('app.frontend_url', config('app.url')), '/').'/'.ltrim($path, '/');
    }
}
