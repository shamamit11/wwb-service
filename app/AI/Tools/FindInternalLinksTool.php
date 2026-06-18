<?php

namespace App\AI\Tools;

use App\Modules\Seo\Data\InternalLinkContextData;
use App\Modules\Seo\Services\SuggestInternalLinksService;

class FindInternalLinksTool
{
    public function __construct(
        private readonly SuggestInternalLinksService $internalLinks,
    ) {}

    /**
     * @param  list<string>  $secondaryKeywords
     * @return list<array<string, mixed>>
     */
    public function suggest(
        string $title,
        ?string $primaryKeyword = null,
        array $secondaryKeywords = [],
        ?string $excerpt = null,
        int $limit = 5,
    ): array {
        return $this->internalLinks->handleForContext(new InternalLinkContextData(
            title: $title,
            excerpt: $excerpt,
            tagNames: $secondaryKeywords,
            focusKeyword: $primaryKeyword,
        ), $limit)->map(static fn ($suggestion) => $suggestion->toArray())->all();
    }
}
