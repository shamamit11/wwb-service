<?php

namespace App\Modules\Posts\Services;

use App\Modules\Posts\Repositories\PostRepository;
use App\Support\SlugGenerator;

class PostSlugResolver
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly SlugGenerator $slugGenerator,
    ) {}

    public function resolve(string $title, ?string $slug = null, ?int $ignoreId = null): string
    {
        return $this->slugGenerator->resolve(
            source: $title,
            preferredSlug: $slug,
            ignoreId: $ignoreId,
            slugExists: fn (string $candidate, ?int $ignoredId): bool => $this->posts->existsBySlug($candidate, $ignoredId),
        );
    }
}
