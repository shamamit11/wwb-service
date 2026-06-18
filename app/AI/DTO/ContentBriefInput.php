<?php

namespace App\AI\DTO;

final readonly class ContentBriefInput extends AgentInput
{
    /**
     * @param  list<string>  $knowledgeBaseContext
     * @param  list<string>  $internalContentContext
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $topic,
        public string $cluster,
        public array $knowledgeBaseContext = [],
        public array $internalContentContext = [],
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
