<?php

namespace App\Modules\Posts\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class PublicPostFiltersData extends DataTransferObject
{
    public function __construct(
        public ?string $search = null,
        public ?string $categorySlug = null,
        public ?string $tagSlug = null,
        public string $sort = '-published_at',
        public int $perPage = 15,
        public bool $returnEmptyWhenSearchBlank = false,
    ) {}
}
