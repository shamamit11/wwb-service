<?php

namespace App\Modules\News\Services;

use App\Modules\News\Data\NewsItemFiltersData;
use App\Modules\News\Repositories\NewsItemRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminNewsItemsService
{
    public function __construct(
        private readonly NewsItemRepository $items,
    ) {}

    public function handle(NewsItemFiltersData $filters): Collection
    {
        return $this->items->searchAdmin($filters);
    }
}
