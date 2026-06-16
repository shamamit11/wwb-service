<?php

namespace App\Modules\Seo\Services;

use App\Models\Category;
use App\Models\KnowledgeBaseEntry;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;

class CanonicalUrlService
{
    public function for(Model $seoable): ?string
    {
        $override = trim((string) ($seoable->seo?->canonical_url ?? ''));

        if ($override !== '') {
            return $override;
        }

        return match ($seoable::class) {
            Post::class => $this->forPost($seoable),
            Category::class => $this->forCategory($seoable),
            KnowledgeBaseEntry::class => $this->forKnowledgeBaseEntry($seoable),
            default => null,
        };
    }

    private function forPost(Post $post): ?string
    {
        if ($post->status !== Post::STATUS_PUBLISHED || $post->visibility !== Post::VISIBILITY_PUBLIC) {
            return null;
        }

        return $this->absolute("{$post->slug}/");
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

    private function absolute(string $path): string
    {
        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }
}
