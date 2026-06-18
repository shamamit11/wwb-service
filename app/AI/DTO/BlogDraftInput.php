<?php

namespace App\AI\DTO;

final readonly class BlogDraftInput extends AgentInput
{
    /**
     * @param  list<string>  $knowledgeBaseContext
     * @param  list<string>  $secondaryKeywords
     * @param  list<array<string, mixed>>  $outline
     * @param  list<string>  $headingStructure
     * @param  list<array<string, mixed>>  $faqSuggestions
     * @param  list<array<string, mixed>>  $existingPostContext
     * @param  list<array<string, mixed>>  $internalLinkContext
     * @param  list<array<string, mixed>>  $imageSuggestions
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public int $contentBriefId,
        public int $contentTopicId,
        public string $title,
        public string $slug,
        public ?string $generationMode = null,
        public ?string $primaryKeyword = null,
        public array $secondaryKeywords = [],
        public ?string $searchIntent = null,
        public ?string $introAngle = null,
        public ?string $targetAudience = null,
        public array $outline = [],
        public array $headingStructure = [],
        public array $faqSuggestions = [],
        public array $knowledgeBaseContext = [],
        public array $existingPostContext = [],
        public array $internalLinkContext = [],
        public array $imageSuggestions = [],
        ?string $provider = null,
        ?string $model = null,
        ?int $timeoutSeconds = null,
        ?int $retryTimes = null,
        ?int $retrySleepMilliseconds = null,
        array $metadata = [],
    ) {
        parent::__construct(
            provider: $provider,
            model: $model,
            timeoutSeconds: $timeoutSeconds,
            retryTimes: $retryTimes,
            retrySleepMilliseconds: $retrySleepMilliseconds,
            metadata: $metadata,
        );
    }
}
