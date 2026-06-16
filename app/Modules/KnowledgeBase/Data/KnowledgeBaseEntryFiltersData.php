<?php

namespace App\Modules\KnowledgeBase\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class KnowledgeBaseEntryFiltersData extends DataTransferObject
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?string $entryType = null,
        public ?int $createdByUserId = null,
        public ?int $featuredMediaId = null,
        public string $sort = '-updated_at',
    ) {}
}
