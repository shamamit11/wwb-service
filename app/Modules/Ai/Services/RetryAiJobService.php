<?php

namespace App\Modules\Ai\Services;

use App\Models\AiJob;

class RetryAiJobService
{
    public function __construct(
        private readonly AiWorkflowOrchestrator $workflows,
    ) {}

    public function handle(AiJob $job): AiJob
    {
        return $this->workflows->retry($job);
    }
}
