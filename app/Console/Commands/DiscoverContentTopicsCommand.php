<?php

namespace App\Console\Commands;

use App\Models\ContentTopic;
use App\Modules\Ai\Data\DiscoverContentTopicsData;
use App\Modules\Ai\Services\AiWorkflowOrchestrator;
use Illuminate\Console\Command;

class DiscoverContentTopicsCommand extends Command
{
    protected $signature = 'ai:discover-topics
        {--cluster= : Approved content cluster to target}
        {--count=10 : Number of topic suggestions to request}
        {--audience= : Optional target audience context}
        {--prompt-template-key= : Optional prompt template key override}
        {--sync : Run immediately instead of dispatching to the queue}';

    protected $description = 'Discover AI-generated content topics inside an approved Wide Web Blog cluster.';

    public function handle(AiWorkflowOrchestrator $service): int
    {
        $cluster = $this->option('cluster');
        $count = (int) $this->option('count');
        $audience = $this->option('audience');
        $promptTemplateKey = $this->option('prompt-template-key');

        if (! is_string($cluster) || $cluster === '') {
            $this->components->error('The --cluster option is required.');

            return self::INVALID;
        }

        if (! in_array($cluster, ContentTopic::CLUSTERS, true)) {
            $this->components->error("Unsupported cluster [{$cluster}].");
            $this->line('Allowed clusters: '.implode(', ', ContentTopic::CLUSTERS));

            return self::INVALID;
        }

        if ($count < 1) {
            $this->components->error('The --count option must be at least 1.');

            return self::INVALID;
        }

        if ($this->option('sync')) {
            $result = $service->runTopicDiscovery(new DiscoverContentTopicsData(
                cluster: $cluster,
                count: $count,
                audience: is_string($audience) && $audience !== '' ? $audience : null,
                promptTemplateKey: is_string($promptTemplateKey) && $promptTemplateKey !== '' ? $promptTemplateKey : null,
                metadata: ['trigger' => 'command_sync'],
            ));

            $savedCount = count($result->metadata['saved_topic_ids'] ?? []);
            $skippedCount = count($result->metadata['skipped_duplicates'] ?? []);

            $this->components->info("Topic discovery completed for [{$cluster}].");
            $this->line("Saved topics: {$savedCount}");
            $this->line("Skipped duplicates: {$skippedCount}");

            return self::SUCCESS;
        }

        $service->dispatchTopicDiscovery(new DiscoverContentTopicsData(
            cluster: $cluster,
            count: $count,
            audience: is_string($audience) && $audience !== '' ? $audience : null,
            promptTemplateKey: is_string($promptTemplateKey) && $promptTemplateKey !== '' ? $promptTemplateKey : null,
            metadata: ['trigger' => 'command_queue'],
        ));

        $this->components->info("Queued topic discovery for [{$cluster}] on the [ai] queue.");

        return self::SUCCESS;
    }
}
