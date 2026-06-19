<?php

namespace App\Modules\Newsletter\Repositories;

use App\Models\NewsletterList;
use App\Modules\Newsletter\Data\CreateNewsletterListData;
use App\Modules\Newsletter\Data\UpdateNewsletterListData;
use Illuminate\Database\Eloquent\Collection;

class EloquentNewsletterListRepository implements NewsletterListRepository
{
    public function create(CreateNewsletterListData $data): NewsletterList
    {
        return NewsletterList::query()->create([
            'name' => $data->name,
            'slug' => $data->slug,
            'description' => $data->description,
            'status' => $data->status,
        ])->refresh();
    }

    public function update(NewsletterList $list, UpdateNewsletterListData $data): NewsletterList
    {
        $list->update([
            'name' => $data->name,
            'slug' => $data->slug,
            'description' => $data->description,
            'status' => $data->status,
        ]);

        return $list->refresh();
    }

    public function delete(NewsletterList $list): void
    {
        $list->delete();
    }

    public function findById(int $id): ?NewsletterList
    {
        return NewsletterList::query()->with('subscribers')->withCount('subscribers')->find($id);
    }

    public function findBySlug(string $slug): ?NewsletterList
    {
        return NewsletterList::query()
            ->with('subscribers')
            ->withCount('subscribers')
            ->where('slug', $slug)
            ->first();
    }

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool
    {
        return NewsletterList::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    /**
     * @return Collection<int, NewsletterList>
     */
    public function getAllOrdered(): Collection
    {
        return NewsletterList::query()
            ->withCount('subscribers')
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }
}
