<?php

namespace App\Modules\Categories\Repositories;

use App\Models\Category;
use App\Modules\Categories\Data\CreateCategoryData;
use App\Modules\Categories\Data\UpdateCategoryData;
use Illuminate\Database\Eloquent\Collection;

class EloquentCategoryRepository implements CategoryRepository
{
    public function create(CreateCategoryData $data): Category
    {
        return Category::query()->create([
            'parent_id' => $data->parentId,
            'created_by_user_id' => $data->createdByUserId,
            'updated_by_user_id' => $data->updatedByUserId,
            'name' => $data->name,
            'slug' => $data->slug,
            'description' => $data->description,
            'is_active' => $data->isActive,
            'sort_order' => $data->sortOrder,
        ]);
    }

    public function update(Category $category, UpdateCategoryData $data): Category
    {
        $category->update([
            'parent_id' => $data->parentId,
            'updated_by_user_id' => $data->updatedByUserId,
            'name' => $data->name,
            'slug' => $data->slug,
            'description' => $data->description,
            'is_active' => $data->isActive,
            'sort_order' => $data->sortOrder,
        ]);

        return $category->refresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function findById(int $id): ?Category
    {
        return Category::query()->find($id);
    }

    public function findBySlug(string $slug): ?Category
    {
        return Category::query()
            ->where('slug', $slug)
            ->first();
    }

    public function findActiveBySlug(string $slug): ?Category
    {
        return Category::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool
    {
        return Category::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    /**
     * @return Collection<int, Category>
     */
    public function getAllOrdered(): Collection
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Category>
     */
    public function getActiveOrdered(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
