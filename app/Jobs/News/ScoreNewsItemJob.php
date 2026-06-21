<?php

namespace App\Jobs\News;

use App\Models\NewsItemScore;
use App\Modules\News\Repositories\NewsItemRepository;
use App\Modules\News\Services\NewsScoringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScoreNewsItemJob implements ShouldQueue
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

    public function handle(NewsItemRepository $items, NewsScoringService $service): void
    {
        $item = $items->findById($this->newsItemId);

        if ($item === null) {
            return;
        }

        $score = $service->handle($item);

        if ($score->decision === NewsItemScore::DECISION_IGNORE) {
            RouteNewsItemJob::dispatch((int) $item->id);

            return;
        }

        ExtractNewsItemContentJob::dispatch((int) $item->id);
    }
}
