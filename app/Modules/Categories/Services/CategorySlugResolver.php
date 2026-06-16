<?php

namespace App\Modules\Categories\Services;

use App\Modules\Categories\Repositories\CategoryRepository;
use Illuminate\Support\Str;

class CategorySlugResolver
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    public function resolve(string $name, ?string $slug = null, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($slug ?: $name);
        $candidate = $baseSlug !== '' ? $baseSlug : Str::lower(Str::ulid()->toBase32());
        $suffix = 2;

        while ($this->categories->existsBySlug($candidate, $ignoreId)) {
            $candidate = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
