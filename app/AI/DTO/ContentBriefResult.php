<?php

namespace App\AI\DTO;

final readonly class ContentBriefResult extends AgentOutput
{
    /**
     * @param  list<array<string, mixed>>  $outline
     * @param  list<string>  $headingStructure
     * @param  list<array<string, mixed>>  $faqSuggestions
     * @param  list<array<string, mixed>>  $internalLinkSuggestions
     * @param  list<string>  $imageIdeas
     * @param  list<string>  $altTextSuggestions
     */
    public function __construct(
        public string $recommendedTitle,
        public string $slug,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
        public ?string $introAngle = null,
        public ?string $targetAudience = null,
        public array $outline = [],
        public array $headingStructure = [],
        public array $faqSuggestions = [],
        public array $internalLinkSuggestions = [],
        public array $imageIdeas = [],
        public array $altTextSuggestions = [],
    ) {}

    /**
     * @return list<array{placement:string,idea:string,alt_text:string}>
     */
    public function imageSuggestions(): array
    {
        $ideas = array_values($this->imageIdeas);
        $altTexts = array_values($this->altTextSuggestions);
        $imageSuggestions = [];

        foreach ($ideas as $index => $idea) {
            $imageSuggestions[] = [
                'placement' => $index === 0 ? 'hero' : "section_{$index}",
                'idea' => $idea,
                'alt_text' => $altTexts[$index] ?? $altTexts[0] ?? $idea,
            ];
        }

        return $imageSuggestions;
    }
}
