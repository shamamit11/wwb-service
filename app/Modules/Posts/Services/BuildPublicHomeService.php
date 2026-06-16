<?php

namespace App\Modules\Posts\Services;

use App\Models\Category;
use App\Models\Post;

class BuildPublicHomeService
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $featured = Post::query()
            ->with(['category', 'tags', 'featuredMedia', 'template', 'seo.ogImageMedia'])
            ->where('status', Post::STATUS_PUBLISHED)
            ->where('visibility', Post::VISIBILITY_PUBLIC)
            ->whereNotNull('published_at')
            ->where('is_featured', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $latest = Post::query()
            ->with(['category', 'tags', 'featuredMedia', 'template', 'seo.ogImageMedia'])
            ->where('status', Post::STATUS_PUBLISHED)
            ->where('visibility', Post::VISIBILITY_PUBLIC)
            ->whereNotNull('published_at')
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        if ($featured->isEmpty()) {
            $featured = $latest->take(5)->values();
        }

        $categories = Category::query()
            ->with('seo.ogImageMedia')
            ->withCount(['posts as published_posts_count' => fn ($query) => $query
                ->where('status', Post::STATUS_PUBLISHED)
                ->where('visibility', Post::VISIBILITY_PUBLIC)
                ->whereNotNull('published_at')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(10)
            ->get();

        return [
            'featured_posts' => $featured,
            'latest_posts' => $latest,
            'categories' => $categories,
            'seo' => [
                'meta_title' => (string) config('app.name'),
                'meta_description' => null,
                'canonical_url' => rtrim((string) config('app.url'), '/').'/',
            ],
        ];
    }
}
