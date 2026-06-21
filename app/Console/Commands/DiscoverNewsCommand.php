<?php

namespace App\Console\Commands;

use App\Jobs\News\DiscoverNewsItemsJob;
use App\Models\Category;
use App\Models\NewsItemScore;
use App\Modules\News\Services\ExtractNewsItemContentService;
use App\Modules\News\Services\NewsDiscoveryService;
use App\Modules\News\Services\NewsRoutingService;
use App\Modules\News\Services\NewsScoringService;
use Illuminate\Console\Command;

class DiscoverNewsCommand extends Command
{
    protected $signature = 'news:discover
        {--category= : Active category slug or ID}
        {--limit=10 : Number of news items to discover}
        {--sync : Run synchronously instead of dispatching jobs}';

    protected $description = 'Discover current news by category, then score, extract, and route it into the editorial pipeline.';

    public function handle(
        NewsDiscoveryService $discovery,
        NewsScoringService $scoring,
        ExtractNewsItemContentService $extraction,
        NewsRoutingService $routing,
    ): int {
        $categories = $this->resolveCategories();

        if ($categories === []) {
            $this->components->error('No active mapped categories were found.');

            return self::INVALID;
        }

        $limit = max(1, (int) $this->option('limit'));

        foreach ($categories as $category) {
            if ($this->option('sync')) {
                $items = $discovery->handle($category, $limit, ['trigger' => 'command_sync']);

                foreach ($items as $item) {
                    $score = $scoring->handle($item);

                    if ($score->decision !== NewsItemScore::DECISION_IGNORE) {
                        $extraction->handle($item);
                    }

                    $routing->handle($item);
                }

                $this->components->info("Processed news discovery synchronously for [{$category->name}].");

                continue;
            }

            DiscoverNewsItemsJob::dispatch((int) $category->id, $limit, ['trigger' => 'command_queue']);
            $this->components->info("Queued news discovery for [{$category->name}].");
        }

        return self::SUCCESS;
    }

    /**
     * @return list<Category>
     */
    private function resolveCategories(): array
    {
        $option = $this->option('category');

        if (is_string($option) && $option !== '') {
            $category = ctype_digit($option)
                ? Category::query()->where('is_active', true)->find((int) $option)
                : Category::query()->where('is_active', true)->where('slug', $option)->first();

            return $category instanceof Category ? [$category] : [];
        }

        $mappedSlugs = array_keys((array) config('news.discovery.category_queries', []));

        return Category::query()
            ->where('is_active', true)
            ->whereIn('slug', $mappedSlugs)
            ->orderBy('sort_order')
            ->get()
            ->all();
    }
}
