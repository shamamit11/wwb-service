<?php

namespace App\AI\Tools;

use App\Modules\Seo\Data\InternalLinkContextData;
use App\Modules\Seo\Services\FindRelatedContentService;

class SearchExistingPostsTool
{
    public function __construct(
        private readonly FindRelatedContentService $relatedContent,
    ) {}

    /**
     * @param  list<string>  $secondaryKeywords
     * @return list<array<string, mixed>>
     */
    public function search(
        string $title,
        ?string $primaryKeyword = null,
        array $secondaryKeywords = [],
        ?string $excerpt = null,
        int $limit = 5,
    ): array {
        return $this->relatedContent->handleForContext(new InternalLinkContextData(
            title: $title,
            excerpt: $excerpt,
            tagNames: $secondaryKeywords,
            focusKeyword: $primaryKeyword,
        ), $limit)->map(static fn ($candidate) => $candidate->toArray())->all();
    }
}
