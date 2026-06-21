<?php

namespace App\Modules\Ai\Services;

use App\Models\AiJob;
use App\Models\ContentTopic;
use App\Modules\Ai\Data\QueueBlogDraftGenerationData;

class QueueBlogDraftGenerationService
{
    public function __construct(
        private readonly AiWorkflowOrchestrator $workflows,
    ) {}

    public function handle(ContentTopic $topic, QueueBlogDraftGenerationData $data): AiJob
    {
        return $this->workflows->queueDraftGeneration($topic, $data);
    }
}
