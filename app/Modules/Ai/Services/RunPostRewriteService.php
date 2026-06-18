<?php

namespace App\Modules\Ai\Services;

class RunPostRewriteService
{
    public function __construct(
        private readonly AiWorkflowOrchestrator $workflows,
    ) {}

    public function handle(int $aiJobId): void
    {
        $this->workflows->runQueuedPostRewrite($aiJobId);
    }
}
