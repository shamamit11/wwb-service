<?php

namespace App\Modules\Ai\Services;

use App\Models\AiJob;
use App\Models\ContentBrief;
use App\Modules\Ai\Data\QueueBlogDraftGenerationData;

class QueueBlogDraftGenerationService
{
    public function __construct(
        private readonly AiWorkflowOrchestrator $workflows,
    ) {}

    public function handle(ContentBrief $brief, QueueBlogDraftGenerationData $data): AiJob
    {
        return $this->workflows->queueDraftGeneration($brief, $data);
    }
}
