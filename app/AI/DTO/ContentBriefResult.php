<?php

namespace App\AI\DTO;

final readonly class ContentBriefResult extends AgentOutput
{
    /**
     * @param  list<array<string, mixed>>  $sections
     * @param  list<array<string, mixed>>  $internalLinks
     * @param  list<array<string, mixed>>  $imageSuggestions
     * @param  array<string, mixed>  $seoHints
     */
    public function __construct(
        public string $title,
        public string $angle,
        public array $sections,
        public array $internalLinks = [],
        public array $imageSuggestions = [],
        public array $seoHints = [],
    ) {}
}
