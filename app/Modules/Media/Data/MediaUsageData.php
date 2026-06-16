<?php

namespace App\Modules\Media\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class MediaUsageData extends DataTransferObject
{
    /**
     * @param  list<MediaUsageReferenceData>  $references
     */
    public function __construct(
        public int $usageCount,
        public array $references,
    ) {}

    public function inUse(): bool
    {
        return $this->usageCount > 0;
    }
}
