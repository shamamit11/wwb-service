<?php

namespace App\Modules\Posts\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class PostStateTransitionData extends DataTransferObject
{
    public function __construct(
        public string $status,
        public ?string $publishedAt,
        public ?string $scheduledFor,
    ) {}
}
