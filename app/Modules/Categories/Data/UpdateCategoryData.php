<?php

namespace App\Modules\Categories\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateCategoryData extends DataTransferObject
{
    public function __construct(
        public ?int $parentId,
        public ?int $updatedByUserId,
        public string $name,
        public string $slug,
        public ?string $description,
        public bool $isActive,
        public int $sortOrder,
    ) {}
}
