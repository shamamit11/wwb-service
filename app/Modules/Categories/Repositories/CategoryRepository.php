<?php

namespace App\Modules\Categories\Repositories;

use App\Models\Category;
use App\Modules\Categories\Data\CreateCategoryData;
use App\Modules\Categories\Data\UpdateCategoryData;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepository
{
    public function create(CreateCategoryData $data): Category;

    public function update(Category $category, UpdateCategoryData $data): Category;

    public function delete(Category $category): void;

    public function findById(int $id): ?Category;

    public function findBySlug(string $slug): ?Category;

    public function findActiveBySlug(string $slug): ?Category;

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool;

    /**
     * @return Collection<int, Category>
     */
    public function getAllOrdered(): Collection;

    /**
     * @return Collection<int, Category>
     */
    public function getActiveOrdered(): Collection;
}
