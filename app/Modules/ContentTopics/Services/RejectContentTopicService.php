<?php

namespace App\Modules\ContentTopics\Services;

use App\Models\ContentTopic;
use App\Modules\ContentTopics\Data\ContentTopicStateTransitionData;
use App\Modules\ContentTopics\Exceptions\InvalidContentTopicStateTransitionException;
use App\Modules\ContentTopics\Repositories\ContentTopicRepository;
use App\Support\AuditActivityLogger;

class RejectContentTopicService
{
    public function __construct(
        private readonly ContentTopicRepository $topics,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(ContentTopic $topic, ?string $notes = null): ContentTopic
    {
        if (! in_array($topic->status, [ContentTopic::STATUS_SUGGESTED, ContentTopic::STATUS_APPROVED], true)) {
            throw new InvalidContentTopicStateTransitionException(
                action: 'reject',
                currentStatus: $topic->status,
                message: "Content topic cannot be rejected from [{$topic->status}] status.",
            );
        }

        $old = $this->auditAttributes($topic);

        $updated = $this->topics->transition($topic, new ContentTopicStateTransitionData(
            status: ContentTopic::STATUS_REJECTED,
            approvedAt: null,
            rejectedAt: now()->toDateTimeString(),
            usedAt: null,
            notes: $notes,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'content-topic.rejected',
            event: 'rejected',
            subject: $updated,
            attributes: $this->auditAttributes($updated),
            old: $old,
        );

        return $updated;
    }

    /**
     * @return array<string, mixed>
     */
    private function auditAttributes(ContentTopic $topic): array
    {
        return [
            'status' => $topic->status,
            'approved_at' => $topic->approved_at?->toISOString(),
            'rejected_at' => $topic->rejected_at?->toISOString(),
            'used_at' => $topic->used_at?->toISOString(),
            'notes' => $topic->notes,
        ];
    }
}
