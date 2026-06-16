<?php

namespace App\Modules\Categories\Services;

use App\Models\Category;
use App\Modules\Categories\Data\CreateCategoryData;
use App\Modules\Categories\Repositories\CategoryRepository;

class CreateCategoryService
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CategorySlugResolver $slugResolver,
    ) {}

    public function handle(CreateCategoryData $data): Category
    {
        return $this->categories->create(new CreateCategoryData(
            parentId: $data->parentId,
            createdByUserId: $data->createdByUserId,
            updatedByUserId: $data->updatedByUserId,
            name: $data->name,
            slug: $this->slugResolver->resolve($data->name, $data->slug),
            description: $data->description,
            isActive: $data->isActive,
            sortOrder: $data->sortOrder,
        ));
    }
}
