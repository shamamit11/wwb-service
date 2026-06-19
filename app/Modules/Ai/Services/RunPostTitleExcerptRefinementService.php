<?php

namespace App\Modules\Ai\Services;

class RunPostTitleExcerptRefinementService
{
    public function __construct(
        private readonly AiWorkflowOrchestrator $workflows,
    ) {}

    public function handle(int $aiJobId): void
    {
        $this->workflows->runQueuedPostTitleExcerptRefinement($aiJobId);
    }
}
