<?php

namespace App\Modules\Seo\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class InternalLinkSuggestionData extends DataTransferObject
{
    /**
     * @param  list<string>  $matchedTerms
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $contentType,
        public int $id,
        public string $title,
        public string $slug,
        public ?string $url,
        public int $score,
        public string $anchorText,
        public string $reason,
        public array $matchedTerms,
        public array $meta = [],
    ) {}
}
