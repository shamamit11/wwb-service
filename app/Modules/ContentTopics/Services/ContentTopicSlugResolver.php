<?php

namespace App\Modules\ContentTopics\Services;

use App\Modules\ContentTopics\Repositories\ContentTopicRepository;
use App\Support\SlugGenerator;

class ContentTopicSlugResolver
{
    public function __construct(
        private readonly ContentTopicRepository $topics,
        private readonly SlugGenerator $slugGenerator,
    ) {}

    public function resolve(string $title, ?string $slug = null, ?int $ignoreId = null): string
    {
        return $this->slugGenerator->resolve(
            source: $title,
            preferredSlug: $slug,
            ignoreId: $ignoreId,
            slugExists: fn (string $candidate, ?int $ignoredId): bool => $this->topics->existsBySlug($candidate, $ignoredId),
        );
    }
}
