<?php

namespace App\Modules\Templates\Services;

use App\Modules\Templates\Repositories\TemplateRepository;
use App\Support\SlugGenerator;

class TemplateSlugResolver
{
    public function __construct(
        private readonly TemplateRepository $templates,
        private readonly SlugGenerator $slugGenerator,
    ) {}

    public function resolve(string $name, ?string $slug = null, ?int $ignoreId = null): string
    {
        return $this->slugGenerator->resolve(
            source: $name,
            preferredSlug: $slug,
            ignoreId: $ignoreId,
            slugExists: fn (string $candidate, ?int $ignoredId): bool => $this->templates->existsBySlug($candidate, $ignoredId),
        );
    }
}
