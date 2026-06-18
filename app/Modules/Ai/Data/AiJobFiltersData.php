<?php

namespace App\Modules\Ai\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class AiJobFiltersData extends DataTransferObject
{
    public function __construct(
        public ?string $status = null,
        public ?string $type = null,
        public ?string $entityType = null,
        public ?int $entityId = null,
        public ?string $provider = null,
        public ?string $model = null,
        public string $sort = '-created_at',
    ) {}
}
