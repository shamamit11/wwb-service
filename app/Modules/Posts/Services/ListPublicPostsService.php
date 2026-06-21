<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Data\PublicPostFiltersData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class ListPublicPostsService
{
    public function handle(PublicPostFiltersData $filters): LengthAwarePaginator
    {
        if ($filters->returnEmptyWhenSearchBlank && blank($filters->search)) {
            return new Paginator(
                items: collect(),
                total: 0,
                perPage: $filters->perPage,
                currentPage: Paginator::resolveCurrentPage(),
                options: [
                    'path' => Paginator::resolveCurrentPath(),
                    'query' => request()->query(),
                ],
            );
        }

        [$sortColumn, $descending] = $this->normalizeSort($filters->sort);

        return $this->baseQuery()
            ->when($filters->search, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                        $inner
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%")
                            ->orWhere('short_description', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('full_article_html', 'like', "%{$search}%")
                        ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%"))
                        ->orWhereHas('tags', fn (Builder $tagQuery) => $tagQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%"));
                });
            })
            ->when($filters->categorySlug, function (Builder $query, string $categorySlug): void {
                $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $categorySlug)->where('is_active', true));
            })
            ->when($filters->tagSlug, function (Builder $query, string $tagSlug): void {
                $query->whereHas('tags', fn (Builder $tagQuery) => $tagQuery->where('slug', $tagSlug)->where('is_active', true));
            })
            ->orderBy($sortColumn, $descending ? 'desc' : 'asc')
            ->orderByDesc('id')
            ->paginate($filters->perPage)
            ->withQueryString();
    }

    protected function baseQuery(): Builder
    {
        return Post::query()
            ->with(['author', 'category', 'tags', 'featuredMedia', 'seo.ogImageMedia'])
            ->where('status', Post::STATUS_PUBLISHED)
            ->where('visibility', Post::VISIBILITY_PUBLIC)
            ->whereNotNull('published_at')
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true));
    }

    /**
     * @return array{0:string,1:bool}
     */
    protected function normalizeSort(string $sort): array
    {
        $descending = str_starts_with($sort, '-');
        $field = ltrim($sort, '-');
        $allowed = ['published_at', 'updated_at', 'title'];

        if (! in_array($field, $allowed, true)) {
            return ['published_at', true];
        }

        return [$field, $descending];
    }
}
