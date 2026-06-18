<?php

namespace App\Modules\Ai\Services;

use App\AI\DTO\AgentResult;
use App\Modules\Ai\Data\DiscoverContentTopicsData;

class RunTopicDiscoveryService
{
    public function __construct(
        private readonly AiWorkflowOrchestrator $workflows,
    ) {}

    public function handle(DiscoverContentTopicsData $data): AgentResult
    {
        return $this->workflows->runTopicDiscovery($data);
    }
}
