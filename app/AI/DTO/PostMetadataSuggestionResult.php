<?php

namespace App\AI\DTO;

final readonly class PostMetadataSuggestionResult extends AgentOutput
{
    /**
     * @param  list<string>  $schemaHints
     */
    public function __construct(
        public ?string $title,
        public ?string $excerpt,
        public ?string $metaTitle,
        public ?string $metaDescription,
        public ?string $focusKeyword,
        public array $schemaHints = [],
        public ?string $rationale = null,
    ) {}
}
