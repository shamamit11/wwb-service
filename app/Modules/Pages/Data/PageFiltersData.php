<?php

namespace App\Modules\Pages\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class PageFiltersData extends DataTransferObject
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?string $type = null,
        public ?string $visibility = null,
        public ?int $createdByUserId = null,
        public string $sort = '-updated_at',
    ) {}
}
