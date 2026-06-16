<?php

namespace App\Modules\KnowledgeBase\Services;

use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;
use App\Support\SlugGenerator;

class KnowledgeBaseEntrySlugResolver
{
    public function __construct(
        private readonly KnowledgeBaseEntryRepository $entries,
        private readonly SlugGenerator $slugGenerator,
    ) {}

    public function resolve(string $title, ?string $slug = null, ?int $ignoreId = null): string
    {
        return $this->slugGenerator->resolve(
            source: $title,
            preferredSlug: $slug,
            ignoreId: $ignoreId,
            slugExists: fn (string $candidate, ?int $ignoredId): bool => $this->entries->existsBySlug($candidate, $ignoredId),
        );
    }
}
