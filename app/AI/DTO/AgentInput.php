<?php

namespace App\AI\DTO;

use App\Infrastructure\Ai\Data\GenerateTextRequest;
use App\Modules\Shared\Data\DataTransferObject;

abstract readonly class AgentInput extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public ?string $provider = null,
        public ?string $model = null,
        public ?int $timeoutSeconds = null,
        public ?int $retryTimes = null,
        public ?int $retrySleepMilliseconds = null,
        public array $metadata = [],
    ) {}

    public function toGenerateTextRequest(string $systemPrompt, string $prompt): GenerateTextRequest
    {
        return new GenerateTextRequest(
            systemPrompt: $systemPrompt,
            prompt: $prompt,
            provider: $this->provider,
            model: $this->model,
            timeoutSeconds: $this->timeoutSeconds,
            retryTimes: $this->retryTimes,
            retrySleepMilliseconds: $this->retrySleepMilliseconds,
        );
    }
}
