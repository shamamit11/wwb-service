<?php

namespace App\Modules\Categories\Services;

use App\Modules\Categories\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;

class ListPublicCategoriesService
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    public function handle(): Collection
    {
        return $this->categories->getActiveOrdered();
    }
}
