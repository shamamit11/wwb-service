<?php

namespace App\Modules\News\Services;

use App\Models\NewsItem;
use App\Modules\News\Repositories\NewsItemRepository;

class ReadNewsItemService
{
    public function __construct(
        private readonly NewsItemRepository $items,
    ) {}

    public function handle(int $id): ?NewsItem
    {
        return $this->items->findById($id);
    }
}
