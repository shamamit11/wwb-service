<?php

namespace App\Modules\Posts\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class SchedulePostData extends DataTransferObject
{
    public function __construct(
        public string $scheduledFor,
    ) {}
}
