<?php

namespace App\Modules\ContentTopics\Services;

use App\Models\ContentTopic;
use App\Modules\Ai\Services\AiAutomationDailyLimitService;
use App\Modules\Ai\Services\DraftGenerationWorkflow;
use App\Modules\Ai\Services\ResolveAutoDraftGenerationDataService;

class AutoAdvanceHighPriorityTopicService
{
    public function __construct(
        private readonly ApproveContentTopicService $approveTopic,
        private readonly ResolveAutoDraftGenerationDataService $resolveAutoDraftGenerationData,
        private readonly DraftGenerationWorkflow $drafts,
        private readonly AiAutomationDailyLimitService $dailyLimits,
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

        if ($data !== null && $this->dailyLimits->canQueueAutomaticDraft()) {
            $this->drafts->queue($topic, $data);
        }

        return $topic;
    }

    private function shouldAutoAdvance(ContentTopic $topic): bool
    {
        if ($topic->isDuplicateDiscovery()) {
            return false;
        }

        if ($topic->priority_score === null) {
            return false;
        }

        return $this->dailyLimits->isHighPriorityScore($topic->priority_score);
    }
}
