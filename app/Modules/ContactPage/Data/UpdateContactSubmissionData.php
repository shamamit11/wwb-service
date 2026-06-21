<?php

namespace App\Modules\ContactPage\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateContactSubmissionData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public int $reviewedByUserId,
        public string $status,
        public ?string $adminNotes,
        public ?array $metadata,
    ) {}
}
