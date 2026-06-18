<?php

namespace App\Modules\Ai\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class AiPromptTemplateFiltersData extends DataTransferObject
{
    public function __construct(
        public ?string $type = null,
        public ?string $status = null,
        public ?string $search = null,
        public string $sort = 'name',
    ) {}
}
