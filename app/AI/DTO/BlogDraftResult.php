<?php

namespace App\AI\DTO;

final readonly class BlogDraftResult extends AgentOutput
{
    /**
     * @param  list<array<string, mixed>>  $faqSuggestions
     * @param  list<string>  $suggestedTags
     * @param  list<string>  $imagePlacementNotes
     * @param  list<string>  $altTextSuggestions
     */
    public function __construct(
        public string $title,
        public string $slug,
        public string $fullArticleHtml,
        public ?array $fullArticleDelta = null,
        public ?string $shortDescription = null,
        public ?string $description = null,
        public ?string $excerpt = null,
        public ?string $seoTitle = null,
        public ?string $metaDescription = null,
        public array $faqSuggestions = [],
        public array $suggestedTags = [],
        public array $imagePlacementNotes = [],
        public array $altTextSuggestions = [],
    ) {}
}
