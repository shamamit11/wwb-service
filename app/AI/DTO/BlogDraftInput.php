<?php

namespace App\AI\DTO;

final readonly class BlogDraftInput extends AgentInput
{
    /**
     * @param  list<string>  $knowledgeBaseContext
     * @param  list<array<string, mixed>>  $sectionOutline
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $title,
        public string $angle,
        public array $sectionOutline,
        public array $knowledgeBaseContext = [],
        public ?string $excerptGuidance = null,
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
