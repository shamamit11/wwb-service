<?php

namespace App\Modules\Tags\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateTagData extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description,
        public bool $isActive,
    ) {}
}
