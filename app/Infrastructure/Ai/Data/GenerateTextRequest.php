<?php

namespace App\Infrastructure\Ai\Data;

final readonly class GenerateTextRequest
{
    public function __construct(
        public string $systemPrompt,
        public string $prompt,
        public ?string $provider = null,
        public ?string $model = null,
        public ?int $timeoutSeconds = null,
        public ?int $retryTimes = null,
        public ?int $retrySleepMilliseconds = null,
    ) {}
}
