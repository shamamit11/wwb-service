<?php

namespace App\Modules\Newsletter\Services;

use App\Modules\Newsletter\Repositories\NewsletterListRepository;
use App\Support\SlugGenerator;

class NewsletterListSlugResolver
{
    public function __construct(
        private readonly NewsletterListRepository $lists,
        private readonly SlugGenerator $slugGenerator,
    ) {}

    public function resolve(string $name, ?string $slug = null, ?int $ignoreId = null): string
    {
        return $this->slugGenerator->resolve(
            source: $name,
            preferredSlug: $slug,
            ignoreId: $ignoreId,
            slugExists: fn (string $candidate, ?int $ignoredId): bool => $this->lists->existsBySlug($candidate, $ignoredId),
        );
    }
}
