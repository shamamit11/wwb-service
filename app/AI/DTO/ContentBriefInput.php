<?php

namespace App\AI\DTO;

final readonly class ContentBriefInput extends AgentInput
{
    /**
     * @param  list<string>  $secondaryKeywords
     * @param  list<string>  $knowledgeBaseContext
     * @param  list<array<string, mixed>>  $existingPostContext
     * @param  list<array<string, mixed>>  $internalLinkContext
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public int $contentTopicId,
        public string $topicTitle,
        public string $cluster,
        public ?string $primaryKeyword = null,
        public array $secondaryKeywords = [],
        public ?string $searchIntent = null,
        public array $knowledgeBaseContext = [],
        public array $existingPostContext = [],
        public array $internalLinkContext = [],
        public ?string $editorialIntent = null,
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
