<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FindPublishedPostBySlugService
{
    public function handle(string $slug): Post
    {
        $post = Post::query()
            ->with(['author', 'category', 'tags', 'featuredMedia', 'template', 'blocks', 'seo.ogImageMedia'])
            ->where('slug', $slug)
            ->where('status', Post::STATUS_PUBLISHED)
            ->where('visibility', Post::VISIBILITY_PUBLIC)
            ->whereNotNull('published_at')
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->first();

        if ($post === null) {
            throw new NotFoundHttpException;
        }

        $relatedPosts = Post::query()
            ->with(['author', 'category', 'featuredMedia'])
            ->whereKeyNot($post->id)
            ->where('status', Post::STATUS_PUBLISHED)
            ->where('visibility', Post::VISIBILITY_PUBLIC)
            ->whereNotNull('published_at')
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->when($post->category_id !== null, fn (Builder $query) => $query->where('category_id', $post->category_id))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $post->setRelation('relatedPosts', $relatedPosts);

        return $post;
    }
}
