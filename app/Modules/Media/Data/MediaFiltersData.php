<?php

namespace App\Modules\Media\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class MediaFiltersData extends DataTransferObject
{
    public function __construct(
        public ?string $search = null,
        public ?string $sourceType = null,
        public ?string $mimeType = null,
        public ?string $status = null,
        public ?bool $used = null,
        public ?bool $isImage = null,
    ) {}
}
