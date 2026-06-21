<?php

namespace App\Modules\ContentTopics\Services;

use App\Models\ContentTopic;
use App\Modules\Ai\Services\DraftGenerationWorkflow;
use App\Modules\Ai\Services\ResolveAutoDraftGenerationDataService;

class AutoAdvanceHighPriorityTopicService
{
    private const AUTO_APPROVAL_PRIORITY_THRESHOLD = 90.0;

    public function __construct(
        private readonly ApproveContentTopicService $approveTopic,
        private readonly ResolveAutoDraftGenerationDataService $resolveAutoDraftGenerationData,
        private readonly DraftGenerationWorkflow $drafts,
    ) {}

    public function handle(ContentTopic $topic): ContentTopic
    {
        if (! $this->shouldAutoAdvance($topic)) {
            return $topic;
        }

        if (in_array($topic->status, [ContentTopic::STATUS_SUGGESTED, ContentTopic::STATUS_REJECTED], true)) {
            return $this->approveTopic->handle($topic, $topic->notes, true);
        }

        if ($topic->status !== ContentTopic::STATUS_APPROVED) {
            return $topic;
        }

        $data = $this->resolveAutoDraftGenerationData->handle($topic);

        if ($data !== null) {
            $this->drafts->queue($topic, $data);
        }

        return $topic;
    }

    private function shouldAutoAdvance(ContentTopic $topic): bool
    {
        if ($topic->priority_score === null) {
            return false;
        }

        return (float) $topic->priority_score > self::AUTO_APPROVAL_PRIORITY_THRESHOLD;
    }
}
