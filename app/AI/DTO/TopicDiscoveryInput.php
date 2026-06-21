<?php

namespace App\AI\DTO;

final readonly class TopicDiscoveryInput extends AgentInput
{
    /**
     * @param  list<string>  $existingTopics
     * @param  list<string>  $knowledgeContext
     * @param  list<string>  $existingTopics
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public int $categoryId,
        public string $categoryName,
        public string $categorySlug,
        public string $cluster,
        public int $targetCount = 10,
        public ?string $audience = null,
        public array $existingTopics = [],
        public array $knowledgeContext = [],
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
