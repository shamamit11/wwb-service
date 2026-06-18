<?php

namespace App\Modules\ContentBriefs\Services;

use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Support\SlugGenerator;

class ContentBriefSlugResolver
{
    public function __construct(
        private readonly ContentBriefRepository $briefs,
        private readonly SlugGenerator $slugGenerator,
    ) {}

    public function resolve(string $title, ?string $slug = null, ?int $ignoreId = null): string
    {
        return $this->slugGenerator->resolve(
            source: $title,
            preferredSlug: $slug,
            ignoreId: $ignoreId,
            slugExists: fn (string $candidate, ?int $ignoredId): bool => $this->briefs->existsBySlug($candidate, $ignoredId),
        );
    }
}
