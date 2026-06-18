<?php

namespace App\Modules\ContentTopics\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class ContentTopicFiltersData extends DataTransferObject
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?string $cluster = null,
        public ?string $source = null,
        public string $sort = '-created_at',
    ) {}
}
