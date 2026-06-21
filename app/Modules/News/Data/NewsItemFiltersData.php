<?php

namespace App\Modules\News\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class NewsItemFiltersData extends DataTransferObject
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?int $categoryId = null,
        public ?string $decision = null,
        public ?string $route = null,
        public string $sort = '-published_at',
    ) {}
}
