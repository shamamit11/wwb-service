<?php

namespace App\Modules\Ai\Data;

use App\Models\AiGenerationStep;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateAiGenerationStepData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $inputPayload
     * @param  array<string, mixed>|null  $outputPayload
     * @param  array<string, mixed>|null  $usagePayload
     */
    public function __construct(
        public int $aiJobId,
        public string $agentName,
        public string $status = AiGenerationStep::STATUS_PENDING,
        public ?array $inputPayload = null,
        public ?array $outputPayload = null,
        public ?array $usagePayload = null,
        public ?string $errorMessage = null,
        public ?string $startedAt = null,
        public ?string $completedAt = null,
        public ?string $failedAt = null,
    ) {}
}
