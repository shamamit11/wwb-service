<?php

namespace App\AI\DTO;

final readonly class BlogDraftResult extends AgentOutput
{
    /**
     * @param  list<array<string, mixed>>  $blocks
     * @param  array<string, mixed>  $seoDraft
     * @param  list<array<string, mixed>>  $imageSuggestions
     */
    public function __construct(
        public string $title,
        public string $markdown,
        public array $blocks,
        public ?string $excerpt = null,
        public array $seoDraft = [],
        public array $imageSuggestions = [],
    ) {}
}
