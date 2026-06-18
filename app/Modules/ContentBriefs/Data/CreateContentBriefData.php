<?php

namespace App\Modules\ContentBriefs\Data;

use App\Models\ContentBrief;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateContentBriefData extends DataTransferObject
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
        public int $contentTopicId,
        public string $title,
        public ?string $slug,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
        public ?string $primaryKeyword = null,
        public array $secondaryKeywords = [],
        public ?string $searchIntent = null,
        public array $outline = [],
        public array $headings = [],
        public array $faqSuggestions = [],
        public array $internalLinkSuggestions = [],
        public array $imageSuggestions = [],
        public string $status = ContentBrief::STATUS_DRAFT,
        public ?string $approvedAt = null,
    ) {}
}
