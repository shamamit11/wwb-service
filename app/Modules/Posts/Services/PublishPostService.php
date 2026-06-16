<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Data\PostStateTransitionData;
use App\Modules\Posts\Exceptions\InvalidPostStateTransitionException;
use App\Modules\Posts\Repositories\PostRepository;
use App\Support\AuditActivityLogger;

class PublishPostService
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(Post $post): Post
    {
        if (! in_array($post->status, [
            Post::STATUS_DRAFT,
            Post::STATUS_SCHEDULED,
            Post::STATUS_UNPUBLISHED,
        ], true)) {
            throw new InvalidPostStateTransitionException(
                action: 'publish',
                currentStatus: $post->status,
                message: "Post cannot be published from [{$post->status}] status.",
            );
        }

        $old = [
            'status' => $post->status,
            'published_at' => $post->published_at?->toISOString(),
            'scheduled_for' => $post->scheduled_for?->toISOString(),
        ];

        $updated = $this->posts->transition($post, new PostStateTransitionData(
            status: Post::STATUS_PUBLISHED,
            publishedAt: now()->toDateTimeString(),
            scheduledFor: null,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'post.published',
            event: 'published',
            subject: $updated,
            attributes: [
                'status' => $updated->status,
                'published_at' => $updated->published_at?->toISOString(),
            ],
            old: $old,
        );

        return $updated;
    }
}
