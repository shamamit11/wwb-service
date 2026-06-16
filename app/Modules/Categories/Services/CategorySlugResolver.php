<?php

namespace App\Modules\Categories\Services;

use App\Modules\Categories\Repositories\CategoryRepository;
use App\Support\SlugGenerator;

class CategorySlugResolver
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly SlugGenerator $slugGenerator,
    ) {}

    public function resolve(string $name, ?string $slug = null, ?int $ignoreId = null): string
    {
        return $this->slugGenerator->resolve(
            source: $name,
            preferredSlug: $slug,
            ignoreId: $ignoreId,
            slugExists: fn (string $candidate, ?int $ignoredId): bool => $this->categories->existsBySlug($candidate, $ignoredId),
        );
    }
}
