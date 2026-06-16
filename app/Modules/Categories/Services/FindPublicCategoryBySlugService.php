<?php

namespace App\Modules\Categories\Services;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FindPublicCategoryBySlugService
{
    /**
     * @return array{category: Category, posts: Collection<int, Post>}
     */
    public function handle(string $slug): array
    {
        $category = Category::query()
            ->with('seo.ogImageMedia')
            ->withCount(['posts as published_posts_count' => fn ($query) => $query
                ->where('status', Post::STATUS_PUBLISHED)
                ->where('visibility', Post::VISIBILITY_PUBLIC)
                ->whereNotNull('published_at')])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if ($category === null) {
            throw new NotFoundHttpException;
        }

        $posts = Post::query()
            ->with(['category', 'tags', 'featuredMedia', 'template', 'seo.ogImageMedia'])
            ->where('category_id', $category->id)
            ->where('status', Post::STATUS_PUBLISHED)
            ->where('visibility', Post::VISIBILITY_PUBLIC)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return [
            'category' => $category,
            'posts' => $posts,
        ];
    }
}
