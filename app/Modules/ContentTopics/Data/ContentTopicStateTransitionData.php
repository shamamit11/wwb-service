<?php

namespace App\Modules\ContentTopics\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class ContentTopicStateTransitionData extends DataTransferObject
{
    public function __construct(
        public string $status,
        public ?string $approvedAt = null,
        public ?string $rejectedAt = null,
        public ?string $usedAt = null,
        public ?string $notes = null,
    ) {}
}
