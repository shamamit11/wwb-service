<?php

namespace App\Modules\Categories\Services;

use App\Models\Category;
use App\Modules\Categories\Data\UpdateCategoryData;
use App\Modules\Categories\Repositories\CategoryRepository;

class UpdateCategoryService
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CategorySlugResolver $slugResolver,
    ) {}

    public function handle(Category $category, UpdateCategoryData $data): Category
    {
        return $this->categories->update($category, new UpdateCategoryData(
            parentId: $data->parentId,
            updatedByUserId: $data->updatedByUserId,
            name: $data->name,
            slug: $this->slugResolver->resolve($data->name, $data->slug, $category->id),
            description: $data->description,
            isActive: $data->isActive,
            sortOrder: $data->sortOrder,
        ));
    }
}
