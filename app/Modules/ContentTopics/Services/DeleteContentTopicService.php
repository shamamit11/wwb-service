<?php

namespace App\Modules\ContentTopics\Services;

use App\Models\ContentTopic;
use App\Modules\ContentTopics\Repositories\ContentTopicRepository;
use App\Support\AuditActivityLogger;

class DeleteContentTopicService
{
    public function __construct(
        private readonly ContentTopicRepository $topics,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(ContentTopic $topic): void
    {
        $attributes = [
            'title' => $topic->title,
            'slug' => $topic->slug,
            'cluster' => $topic->cluster,
            'status' => $topic->status,
        ];

        $this->topics->delete($topic);

        $this->audit->log(
            logName: 'content',
            description: 'content-topic.deleted',
            event: 'deleted',
            subject: $topic,
            old: $attributes,
        );
    }
}
