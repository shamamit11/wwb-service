<?php

namespace App\Modules\Media\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class MediaUsageReferenceData extends DataTransferObject
{
    public function __construct(
        public string $type,
        public string $label,
        public int $count,
    ) {}
}
