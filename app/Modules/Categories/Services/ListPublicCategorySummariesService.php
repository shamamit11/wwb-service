<?php

namespace App\Modules\Categories\Services;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class ListPublicCategorySummariesService
{
    public function handle(): Collection
    {
        return Category::query()
            ->with('seo.ogImageMedia')
            ->withCount(['posts as published_posts_count' => fn ($query) => $query
                ->where('status', Post::STATUS_PUBLISHED)
                ->where('visibility', Post::VISIBILITY_PUBLIC)
                ->whereNotNull('published_at')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
