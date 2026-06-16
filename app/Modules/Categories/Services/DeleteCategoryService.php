<?php

namespace App\Modules\Categories\Services;

use App\Models\Category;
use App\Modules\Categories\Repositories\CategoryRepository;
use App\Support\AuditActivityLogger;

class DeleteCategoryService
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(Category $category): void
    {
        $attributes = [
            'name' => $category->name,
            'slug' => $category->slug,
            'is_active' => (bool) $category->is_active,
            'sort_order' => $category->sort_order,
        ];

        $this->categories->delete($category);

        $this->audit->log(
            logName: 'content',
            description: 'category.deleted',
            event: 'deleted',
            subject: $category,
            attributes: $attributes,
        );
    }
}
