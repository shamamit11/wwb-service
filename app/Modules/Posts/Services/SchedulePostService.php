<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Data\PostStateTransitionData;
use App\Modules\Posts\Data\SchedulePostData;
use App\Modules\Posts\Exceptions\InvalidPostStateTransitionException;
use App\Modules\Posts\Repositories\PostRepository;
use App\Support\AuditActivityLogger;

class SchedulePostService
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(Post $post, SchedulePostData $data): Post
    {
        if (! in_array($post->status, [
            Post::STATUS_DRAFT,
            Post::STATUS_SCHEDULED,
            Post::STATUS_UNPUBLISHED,
        ], true)) {
            throw new InvalidPostStateTransitionException(
                action: 'schedule',
                currentStatus: $post->status,
                message: "Post cannot be scheduled from [{$post->status}] status.",
            );
        }

        $old = [
            'status' => $post->status,
            'scheduled_for' => $post->scheduled_for?->toISOString(),
            'published_at' => $post->published_at?->toISOString(),
        ];

        $updated = $this->posts->transition($post, new PostStateTransitionData(
            status: Post::STATUS_SCHEDULED,
            publishedAt: null,
            scheduledFor: $data->scheduledFor,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'post.scheduled',
            event: 'scheduled',
            subject: $updated,
            attributes: [
                'status' => $updated->status,
                'scheduled_for' => $updated->scheduled_for?->toISOString(),
            ],
            old: $old,
        );

        return $updated;
    }
}
