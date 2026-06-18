<?php

namespace App\Modules\ContentBriefs\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class ContentBriefFiltersData extends DataTransferObject
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?int $contentTopicId = null,
        public string $sort = '-created_at',
    ) {}
}
