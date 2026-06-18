<?php

namespace App\AI\DTO;

final readonly class TopicDiscoveryInput extends AgentInput
{
    /**
     * @param  list<string>  $contentClusters
     * @param  list<string>  $existingTopics
     * @param  list<string>  $publishedPostTitles
     * @param  list<string>  $knowledgeBaseContext
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public array $contentClusters,
        public array $existingTopics = [],
        public array $publishedPostTitles = [],
        public array $knowledgeBaseContext = [],
        public int $maxTopics = 10,
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
