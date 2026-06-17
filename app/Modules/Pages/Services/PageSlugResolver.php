<?php

namespace App\Modules\Pages\Services;

use App\Modules\Pages\Repositories\PageRepository;
use App\Support\SlugGenerator;

class PageSlugResolver
{
    public function __construct(
        private readonly PageRepository $pages,
        private readonly SlugGenerator $slugGenerator,
    ) {}

    public function resolve(string $title, ?string $slug = null, ?int $ignoreId = null): string
    {
        return $this->slugGenerator->resolve(
            source: $title,
            preferredSlug: $slug,
            ignoreId: $ignoreId,
            slugExists: fn (string $candidate, ?int $ignoredId): bool => $this->pages->existsBySlug($candidate, $ignoredId),
        );
    }
}
