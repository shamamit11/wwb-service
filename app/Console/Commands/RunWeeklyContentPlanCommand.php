<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Modules\Ai\Data\DiscoverContentTopicsData;
use App\Modules\Ai\Services\AiWorkflowOrchestrator;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class RunWeeklyContentPlanCommand extends Command
{
    protected $signature = 'ai:run-weekly-content-plan
        {--day= : Optional weekday override such as monday or sunday}
        {--sync : Run immediately instead of dispatching to the queue}';

    protected $description = 'Run the scheduled AI content plan for the current weekday.';

    public function handle(AiWorkflowOrchestrator $workflows): int
    {
        $day = $this->resolveDay();
        $plan = $this->planForDay($day);

        if ($plan === null) {
            $this->components->error("Unsupported weekday [{$day}].");

            return self::INVALID;
        }

        $category = Category::query()
            ->where('is_active', true)
            ->where('slug', $plan['category_slug'])
            ->first();

        if (! $category instanceof Category) {
            $this->components->error("Active category [{$plan['category_slug']}] could not be found for the weekly content plan.");

            return self::FAILURE;
        }

        $metadata = [
            'trigger' => 'weekly_content_plan',
            'content_plan_day' => $day,
            'content_plan_theme' => $plan['theme'],
            'content_plan_format' => $plan['format'],
        ];

        $data = new DiscoverContentTopicsData(
            categoryId: (int) $category->id,
            count: 2,
            audience: $plan['audience'],
            metadata: $metadata,
        );

        if ($this->option('sync')) {
            $result = $workflows->runTopicDiscovery($data);
            $savedCount = count($result->metadata['saved_topic_ids'] ?? []);

            $this->components->info("Weekly content plan completed for {$day} using [{$category->name}].");
            $this->line("Saved topics: {$savedCount}");

            return self::SUCCESS;
        }

        $workflows->dispatchTopicDiscovery($data);

        $this->components->info("Queued weekly content plan for {$day} using [{$category->name}] on the [ai] queue.");

        return self::SUCCESS;
    }

    private function resolveDay(): string
    {
        $override = $this->option('day');

        if (is_string($override) && $override !== '') {
            return strtolower(trim($override));
        }

        return strtolower(CarbonImmutable::now()->englishDayOfWeek);
    }

    /**
     * @return array{category_slug:string,theme:string,audience:string,format:string}|null
     */
    private function planForDay(string $day): ?array
    {
        return match ($day) {
            'monday' => [
                'category_slug' => 'ai-tools',
                'theme' => 'AI Tools',
                'audience' => 'Developers and operators evaluating practical AI tools.',
                'format' => 'standard',
            ],
            'tuesday' => [
                'category_slug' => 'seo',
                'theme' => 'SEO / Blogging',
                'audience' => 'Technical bloggers and SEO-focused content teams.',
                'format' => 'standard',
            ],
            'wednesday' => [
                'category_slug' => 'ai-agents',
                'theme' => 'AI Tutorial',
                'audience' => 'Developers looking for practical AI tutorials and walkthroughs.',
                'format' => 'tutorial',
            ],
            'thursday' => [
                'category_slug' => 'content-marketing',
                'theme' => 'Content Creation',
                'audience' => 'Content creators building repeatable publishing systems.',
                'format' => 'standard',
            ],
            'friday' => [
                'category_slug' => 'productivity-automation',
                'theme' => 'Productivity / Automation',
                'audience' => 'Builders improving workflows through automation and operational leverage.',
                'format' => 'standard',
            ],
            'saturday' => [
                'category_slug' => 'developer-ai',
                'theme' => 'Software Development (Laravel, AI, MCP, Agents)',
                'audience' => 'Software developers working across Laravel, AI, MCP, and agent systems.',
                'format' => 'standard',
            ],
            'sunday' => [
                'category_slug' => 'developer-ai',
                'theme' => 'Long-form Pillar Article',
                'audience' => 'Technical readers looking for authoritative long-form pillar content.',
                'format' => 'pillar',
            ],
            default => null,
        };
    }
}
