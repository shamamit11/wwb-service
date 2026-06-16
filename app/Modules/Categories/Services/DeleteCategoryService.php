<?php

namespace App\Modules\Categories\Services;

use App\Models\Category;
use App\Modules\Categories\Repositories\CategoryRepository;

class DeleteCategoryService
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    public function handle(Category $category): void
    {
        $this->categories->delete($category);
    }
}
