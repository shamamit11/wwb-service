<?php

namespace App\Infrastructure\Ai\Data;

final readonly class TextGenerationResult
{
    public function __construct(
        public string $content,
        public ?string $provider,
        public ?string $model,
        public AiUsageData $usage,
    ) {}
}
