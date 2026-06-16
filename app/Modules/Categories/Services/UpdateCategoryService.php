<?php

namespace App\Modules\Categories\Services;

use App\Models\Category;
use App\Modules\Categories\Data\UpdateCategoryData;
use App\Modules\Categories\Repositories\CategoryRepository;
use App\Support\AuditActivityLogger;

class UpdateCategoryService
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CategorySlugResolver $slugResolver,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(Category $category, UpdateCategoryData $data): Category
    {
        $old = [
            'name' => $category->name,
            'slug' => $category->slug,
            'is_active' => (bool) $category->is_active,
            'sort_order' => $category->sort_order,
        ];

        $updated = $this->categories->update($category, new UpdateCategoryData(
            parentId: $data->parentId,
            updatedByUserId: $data->updatedByUserId,
            name: $data->name,
            slug: $this->slugResolver->resolve($data->name, $data->slug, $category->id),
            description: $data->description,
            isActive: $data->isActive,
            sortOrder: $data->sortOrder,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'category.updated',
            event: 'updated',
            subject: $updated,
            attributes: [
                'name' => $updated->name,
                'slug' => $updated->slug,
                'is_active' => (bool) $updated->is_active,
                'sort_order' => $updated->sort_order,
            ],
            old: $old,
        );

        return $updated;
    }
}
