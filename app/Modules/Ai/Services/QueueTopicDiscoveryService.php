<?php

namespace App\Modules\Ai\Services;

use App\Models\AiJob;
use App\Modules\Ai\Data\DiscoverContentTopicsData;

class QueueTopicDiscoveryService
{
    public function __construct(
        private readonly AiWorkflowOrchestrator $workflows,
    ) {}

    public function handle(DiscoverContentTopicsData $data): AiJob
    {
        return $this->workflows->dispatchTopicDiscovery($data);
    }
}
