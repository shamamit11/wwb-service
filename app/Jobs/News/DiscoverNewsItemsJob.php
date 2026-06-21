<?php

namespace App\Jobs\News;

use App\Models\Category;
use App\Modules\News\Services\NewsDiscoveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DiscoverNewsItemsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public int $categoryId,
        public int $limit = 10,
        public array $metadata = [],
    ) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(NewsDiscoveryService $service): void
    {
        $category = Category::query()
            ->where('is_active', true)
            ->find($this->categoryId);

        if (! $category instanceof Category) {
            return;
        }

        foreach ($service->handle($category, $this->limit, $this->metadata) as $item) {
            ScoreNewsItemJob::dispatch((int) $item->id);
        }
    }
}
