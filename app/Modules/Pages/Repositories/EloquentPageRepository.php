<?php

namespace App\Modules\Pages\Repositories;

use App\Models\Page;
use App\Modules\Pages\Data\CreatePageData;
use App\Modules\Pages\Data\PageFiltersData;
use App\Modules\Pages\Data\UpdatePageData;
use Illuminate\Database\Eloquent\Collection;

class EloquentPageRepository implements PageRepository
{
    public function create(CreatePageData $data): Page
    {
        return Page::query()->create([
            'created_by_user_id' => $data->createdByUserId,
            'updated_by_user_id' => $data->updatedByUserId,
            'title' => $data->title,
            'slug' => $data->slug,
            'type' => $data->type,
            'status' => $data->status,
            'summary' => $data->summary,
            'content_markdown' => $data->contentMarkdown,
            'visibility' => $data->visibility,
            'published_at' => $data->publishedAt,
            'scheduled_for' => $data->scheduledFor,
            'meta' => $data->meta,
        ])->load($this->relations());
    }

    public function update(Page $page, UpdatePageData $data): Page
    {
        $page->update([
            'updated_by_user_id' => $data->updatedByUserId,
            'title' => $data->title,
            'slug' => $data->slug,
            'type' => $data->type,
            'status' => $data->status,
            'summary' => $data->summary,
            'content_markdown' => $data->contentMarkdown,
            'visibility' => $data->visibility,
            'published_at' => $data->publishedAt,
            'scheduled_for' => $data->scheduledFor,
            'meta' => $data->meta,
        ]);

        return $page->refresh()->load($this->relations());
    }

    public function delete(Page $page): void
    {
        $page->delete();
    }

    public function findById(int $id): ?Page
    {
        return Page::query()
            ->with($this->relations())
            ->find($id);
    }

    public function findBySlug(string $slug): ?Page
    {
        return Page::query()
            ->with($this->relations())
            ->where('slug', $slug)
            ->first();
    }

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool
    {
        return Page::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    /**
     * @return Collection<int, Page>
     */
    public function searchAdmin(PageFiltersData $filters): Collection
    {
        [$sortColumn, $descending] = $this->normalizeSort($filters->sort);

        return Page::query()
            ->with($this->relations())
            ->when($filters->search, function ($query, string $search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhere('content_markdown', 'like', "%{$search}%");
                });
            })
            ->when($filters->status, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters->type, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters->visibility, fn ($query, string $visibility) => $query->where('visibility', $visibility))
            ->when($filters->createdByUserId, fn ($query, int $createdByUserId) => $query->where('created_by_user_id', $createdByUserId))
            ->orderBy($sortColumn, $descending ? 'desc' : 'asc')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return list<string>
     */
    private function relations(): array
    {
        return ['createdBy', 'updatedBy', 'seo'];
    }

    /**
     * @return array{0:string,1:bool}
     */
    private function normalizeSort(string $sort): array
    {
        $descending = str_starts_with($sort, '-');
        $field = ltrim($sort, '-');
        $allowed = ['title', 'created_at', 'updated_at', 'published_at'];

        if (! in_array($field, $allowed, true)) {
            return ['updated_at', true];
        }

        return [$field, $descending];
    }
}
