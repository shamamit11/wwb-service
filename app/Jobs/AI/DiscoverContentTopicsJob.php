<?php

namespace App\Jobs\AI;

use App\Modules\Ai\Data\DiscoverContentTopicsData;
use App\Modules\Ai\Services\RunTopicDiscoveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DiscoverContentTopicsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $cluster,
        public int $count = 10,
        public ?string $audience = null,
        public ?string $promptTemplateKey = null,
        public array $metadata = [],
    ) {
        $this->onQueue('ai');
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(RunTopicDiscoveryService $service): void
    {
        $service->handle(new DiscoverContentTopicsData(
            cluster: $this->cluster,
            count: $this->count,
            audience: $this->audience,
            promptTemplateKey: $this->promptTemplateKey,
            metadata: $this->metadata,
        ));
    }
}
