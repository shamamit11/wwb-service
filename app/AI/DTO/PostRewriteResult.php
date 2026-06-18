<?php

namespace App\AI\DTO;

final readonly class PostRewriteResult extends AgentOutput
{
    /**
     * @param  list<array<string, mixed>>  $contentBlocks
     * @param  list<string>  $suggestedTags
     * @param  list<string>  $imagePlacementNotes
     * @param  list<string>  $altTextSuggestions
     */
    public function __construct(
        public ?string $title,
        public ?string $slug,
        public ?string $excerpt,
        public array $contentBlocks = [],
        public ?string $seoTitle = null,
        public ?string $metaDescription = null,
        public array $suggestedTags = [],
        public array $imagePlacementNotes = [],
        public array $altTextSuggestions = [],
        public string $scope = 'full_draft',
    ) {}
}
