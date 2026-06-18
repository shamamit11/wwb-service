<?php

namespace App\Modules\ContentTopics\Services;

use App\Models\ContentTopic;
use App\Modules\ContentTopics\Data\ContentTopicStateTransitionData;
use App\Modules\ContentTopics\Exceptions\InvalidContentTopicStateTransitionException;
use App\Modules\ContentTopics\Repositories\ContentTopicRepository;
use App\Support\AuditActivityLogger;

class MarkContentTopicUsedService
{
    public function __construct(
        private readonly ContentTopicRepository $topics,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(ContentTopic $topic, ?string $notes = null): ContentTopic
    {
        if ($topic->status !== ContentTopic::STATUS_APPROVED) {
            throw new InvalidContentTopicStateTransitionException(
                action: 'mark-used',
                currentStatus: $topic->status,
                message: "Content topic cannot be marked used from [{$topic->status}] status.",
            );
        }

        $old = $this->auditAttributes($topic);

        $updated = $this->topics->transition($topic, new ContentTopicStateTransitionData(
            status: ContentTopic::STATUS_USED,
            approvedAt: $topic->approved_at?->toDateTimeString(),
            rejectedAt: null,
            usedAt: now()->toDateTimeString(),
            notes: $notes,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'content-topic.marked-used',
            event: 'marked-used',
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
