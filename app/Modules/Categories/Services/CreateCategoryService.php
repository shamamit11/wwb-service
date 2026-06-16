<?php

namespace App\Modules\Categories\Services;

use App\Models\Category;
use App\Modules\Categories\Data\CreateCategoryData;
use App\Modules\Categories\Repositories\CategoryRepository;
use App\Support\AuditActivityLogger;

class CreateCategoryService
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CategorySlugResolver $slugResolver,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(CreateCategoryData $data): Category
    {
        $category = $this->categories->create(new CreateCategoryData(
            parentId: $data->parentId,
            createdByUserId: $data->createdByUserId,
            updatedByUserId: $data->updatedByUserId,
            name: $data->name,
            slug: $this->slugResolver->resolve($data->name, $data->slug),
            description: $data->description,
            isActive: $data->isActive,
            sortOrder: $data->sortOrder,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'category.created',
            event: 'created',
            subject: $category,
            attributes: [
                'name' => $category->name,
                'slug' => $category->slug,
                'is_active' => (bool) $category->is_active,
                'sort_order' => $category->sort_order,
            ],
        );

        return $category;
    }
}
