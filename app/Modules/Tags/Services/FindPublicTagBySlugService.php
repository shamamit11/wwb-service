<?php

namespace App\Modules\Tags\Services;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FindPublicTagBySlugService
{
    /**
     * @return array{tag: Tag, posts: Collection<int, Post>}
     */
    public function handle(string $slug): array
    {
        $tag = Tag::query()
            ->withCount(['posts as published_posts_count' => fn ($query) => $query
                ->where('status', Post::STATUS_PUBLISHED)
                ->where('visibility', Post::VISIBILITY_PUBLIC)
                ->whereNotNull('published_at')
                ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if ($tag === null || (int) $tag->published_posts_count < 1) {
            throw new NotFoundHttpException;
        }

        $posts = Post::query()
            ->with(['category', 'tags', 'featuredMedia', 'template', 'seo.ogImageMedia'])
            ->where('status', Post::STATUS_PUBLISHED)
            ->where('visibility', Post::VISIBILITY_PUBLIC)
            ->whereNotNull('published_at')
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->whereHas('tags', fn ($query) => $query->where('tags.id', $tag->id))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return [
            'tag' => $tag,
            'posts' => $posts,
        ];
    }
}
