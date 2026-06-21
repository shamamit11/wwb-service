<?php

namespace App\Jobs\News;

use App\Modules\News\Repositories\NewsItemRepository;
use App\Modules\News\Services\NewsRoutingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RouteNewsItemJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $newsItemId,
    ) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(NewsItemRepository $items, NewsRoutingService $service): void
    {
        $item = $items->findById($this->newsItemId);

        if ($item === null) {
            return;
        }

        $service->handle($item);
    }
}
