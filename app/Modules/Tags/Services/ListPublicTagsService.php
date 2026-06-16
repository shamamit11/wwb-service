<?php

namespace App\Modules\Tags\Services;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class ListPublicTagsService
{
    public function handle(): Collection
    {
        return Tag::query()
            ->withCount(['posts as published_posts_count' => fn ($query) => $query
                ->where('status', Post::STATUS_PUBLISHED)
                ->where('visibility', Post::VISIBILITY_PUBLIC)
                ->whereNotNull('published_at')
                ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))])
            ->whereHas('posts', fn ($query) => $query
                ->where('status', Post::STATUS_PUBLISHED)
                ->where('visibility', Post::VISIBILITY_PUBLIC)
                ->whereNotNull('published_at')
                ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true)))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
