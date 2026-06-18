<?php

namespace App\Modules\Ai\Services;

class RunBlogDraftGenerationService
{
    public function __construct(
        private readonly AiWorkflowOrchestrator $workflows,
    ) {}

    public function handle(int $aiJobId): void
    {
        $this->workflows->runQueuedDraftGeneration($aiJobId);
    }
}
