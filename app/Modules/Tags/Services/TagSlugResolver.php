<?php

namespace App\Modules\Tags\Services;

use App\Modules\Tags\Repositories\TagRepository;
use App\Support\SlugGenerator;

class TagSlugResolver
{
    public function __construct(
        private readonly TagRepository $tags,
        private readonly SlugGenerator $slugGenerator,
    ) {}

    public function resolve(string $name, ?string $slug = null, ?int $ignoreId = null): string
    {
        return $this->slugGenerator->resolve(
            source: $name,
            preferredSlug: $slug,
            ignoreId: $ignoreId,
            slugExists: fn (string $candidate, ?int $ignoredId): bool => $this->tags->existsBySlug($candidate, $ignoredId),
        );
    }
}
