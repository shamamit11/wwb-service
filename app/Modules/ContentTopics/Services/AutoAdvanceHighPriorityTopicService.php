<?php

namespace App\Modules\ContentTopics\Services;

use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Modules\Ai\Services\ContentBriefWorkflow;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Modules\ContentBriefs\Services\ContinueContentBriefToDraftService;

class AutoAdvanceHighPriorityTopicService
{
    private const AUTO_APPROVAL_PRIORITY_THRESHOLD = 90.0;

    public function __construct(
        private readonly ApproveContentTopicService $approveTopic,
        private readonly ContentBriefWorkflow $briefWorkflow,
        private readonly ContentBriefRepository $briefs,
        private readonly ContinueContentBriefToDraftService $continueBriefToDraft,
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

        $brief = $this->briefs->findByTopicId((int) $topic->id);

        if (! $brief instanceof ContentBrief) {
            $this->briefWorkflow->queue($topic, autoContinueToDraft: true);

            return $topic;
        }

        $this->continueBriefToDraft->handle($brief);

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
