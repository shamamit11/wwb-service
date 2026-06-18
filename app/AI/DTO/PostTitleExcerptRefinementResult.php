<?php

namespace App\AI\DTO;

final readonly class PostTitleExcerptRefinementResult extends AgentOutput
{
    /**
     * @param  list<string>  $headlineVariations
     * @param  list<string>  $excerptVariations
     */
    public function __construct(
        public ?string $recommendedTitle,
        public ?string $recommendedExcerpt,
        public array $headlineVariations = [],
        public array $excerptVariations = [],
        public ?string $rationale = null,
    ) {}
}
