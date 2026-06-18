<?php

namespace App\Modules\Ai\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateAiJobCostData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public int $aiJobId,
        public ?int $aiGenerationStepId = null,
        public ?string $provider = null,
        public ?string $model = null,
        public int $inputTokens = 0,
        public int $outputTokens = 0,
        public int $totalTokens = 0,
        public ?string $estimatedCost = null,
        public ?string $actualCost = null,
        public ?string $currency = null,
        public ?array $metadata = null,
    ) {}
}
