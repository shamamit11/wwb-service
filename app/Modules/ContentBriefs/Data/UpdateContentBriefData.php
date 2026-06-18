<?php

namespace App\Modules\ContentBriefs\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateContentBriefData extends DataTransferObject
{
    /**
     * @param  list<string>  $secondaryKeywords
     * @param  list<array<string, mixed>>  $outline
     * @param  list<string>  $headings
     * @param  list<array<string, mixed>>  $faqSuggestions
     * @param  list<array<string, mixed>>  $internalLinkSuggestions
     * @param  list<array<string, mixed>>  $imageSuggestions
     */
    public function __construct(
        public ?string $title = null,
        public ?string $slug = null,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
        public ?string $primaryKeyword = null,
        public ?array $secondaryKeywords = null,
        public ?string $searchIntent = null,
        public ?array $outline = null,
        public ?array $headings = null,
        public ?array $faqSuggestions = null,
        public ?array $internalLinkSuggestions = null,
        public ?array $imageSuggestions = null,
        public ?string $status = null,
    ) {}
}
