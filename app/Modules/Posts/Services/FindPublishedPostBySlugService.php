<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
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

        return $post;
    }
}
