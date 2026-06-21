<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Modules\Ai\Data\DiscoverContentTopicsData;
use App\Modules\Ai\Services\AiWorkflowOrchestrator;
use App\Modules\Ai\Services\ResolveTopicDiscoveryClusterService;
use Illuminate\Console\Command;

class DiscoverContentTopicsCommand extends Command
{
    protected $signature = 'ai:discover-topics
        {--category= : Active category slug or ID to target}
        {--count=10 : Number of topic suggestions to request}
        {--audience= : Optional target audience context}
        {--sync : Run immediately instead of dispatching to the queue}';

    protected $description = 'Discover AI-generated content topics for an active category.';

    public function handle(AiWorkflowOrchestrator $service, ResolveTopicDiscoveryClusterService $clusters): int
    {
        $categoryOption = $this->option('category');
        $count = (int) $this->option('count');
        $audience = $this->option('audience');
        if (! is_string($categoryOption) || $categoryOption === '') {
            $this->components->error('The --category option is required.');

            return self::INVALID;
        }

        $category = ctype_digit($categoryOption)
            ? Category::query()->where('is_active', true)->find((int) $categoryOption)
            : Category::query()->where('is_active', true)->where('slug', $categoryOption)->first();

        if (! $category instanceof Category) {
            $this->components->error("Active category [{$categoryOption}] could not be found.");

            return self::INVALID;
        }

        if ($clusters->forCategory($category) === null) {
            $this->components->error("Category [{$category->slug}] is not mapped to a supported topic discovery cluster.");
            $this->line('Supported category slugs: '.implode(', ', $clusters->supportedCategorySlugs()));

            return self::INVALID;
        }

        if ($count < 1) {
            $this->components->error('The --count option must be at least 1.');

            return self::INVALID;
        }

        if ($this->option('sync')) {
            $result = $service->runTopicDiscovery(new DiscoverContentTopicsData(
                categoryId: (int) $category->id,
                count: $count,
                audience: is_string($audience) && $audience !== '' ? $audience : null,
                metadata: ['trigger' => 'command_sync'],
            ));

            $savedCount = count($result->metadata['saved_topic_ids'] ?? []);
            $skippedCount = count($result->metadata['skipped_duplicates'] ?? []);

            $this->components->info("Topic discovery completed for [{$category->name}].");
            $this->line("Saved topics: {$savedCount}");
            $this->line("Skipped duplicates: {$skippedCount}");

            return self::SUCCESS;
        }

        $service->dispatchTopicDiscovery(new DiscoverContentTopicsData(
            categoryId: (int) $category->id,
            count: $count,
            audience: is_string($audience) && $audience !== '' ? $audience : null,
            metadata: ['trigger' => 'command_queue'],
        ));

        $this->components->info("Queued topic discovery for [{$category->name}] on the [ai] queue.");

        return self::SUCCESS;
    }
}
