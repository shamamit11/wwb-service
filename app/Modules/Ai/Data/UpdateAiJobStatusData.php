<?php

namespace App\Modules\Ai\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateAiJobStatusData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $outputPayload
     * @param  array<string, mixed>|null  $usagePayload
     */
    public function __construct(
        public string $status,
        public ?array $outputPayload = null,
        public ?array $usagePayload = null,
        public ?string $errorMessage = null,
        public ?string $startedAt = null,
        public ?string $completedAt = null,
        public ?string $failedAt = null,
    ) {}
}
