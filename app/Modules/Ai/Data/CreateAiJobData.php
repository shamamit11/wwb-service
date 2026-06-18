<?php

namespace App\Modules\Ai\Data;

use App\Models\AiJob;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateAiJobData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $inputPayload
     * @param  array<string, mixed>|null  $outputPayload
     * @param  array<string, mixed>|null  $usagePayload
     */
    public function __construct(
        public string $type,
        public string $status = AiJob::STATUS_PENDING,
        public ?string $entityType = null,
        public ?int $entityId = null,
        public ?string $provider = null,
        public ?string $model = null,
        public ?array $inputPayload = null,
        public ?array $outputPayload = null,
        public ?array $usagePayload = null,
        public ?string $errorMessage = null,
        public int $attempts = 1,
        public ?int $retryOfAiJobId = null,
        public ?string $startedAt = null,
        public ?string $completedAt = null,
        public ?string $failedAt = null,
    ) {}
}
