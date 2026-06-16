<?php

namespace App\Modules\Categories\Services;

use App\Models\Category;
use App\Modules\Categories\Repositories\CategoryRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FindActiveCategoryBySlugService
{
    public function __construct(private readonly CategoryRepository $categories) {}

    public function handle(string $slug): Category
    {
        $category = $this->categories->findActiveBySlug($slug);

        if (! $category) {
            throw new NotFoundHttpException;
        }

        return $category;
    }
}
