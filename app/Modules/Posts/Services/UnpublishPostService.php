<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Data\PostStateTransitionData;
use App\Modules\Posts\Exceptions\InvalidPostStateTransitionException;
use App\Modules\Posts\Repositories\PostRepository;
use App\Support\AuditActivityLogger;

class UnpublishPostService
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(Post $post): Post
    {
        if (! in_array($post->status, [
            Post::STATUS_PUBLISHED,
            Post::STATUS_SCHEDULED,
        ], true)) {
            throw new InvalidPostStateTransitionException(
                action: 'unpublish',
                currentStatus: $post->status,
                message: "Post cannot be unpublished from [{$post->status}] status.",
            );
        }

        $old = [
            'status' => $post->status,
            'published_at' => $post->published_at?->toISOString(),
            'scheduled_for' => $post->scheduled_for?->toISOString(),
        ];

        $updated = $this->posts->transition($post, new PostStateTransitionData(
            status: Post::STATUS_UNPUBLISHED,
            publishedAt: null,
            scheduledFor: null,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'post.unpublished',
            event: 'unpublished',
            subject: $updated,
            attributes: [
                'status' => $updated->status,
                'published_at' => $updated->published_at?->toISOString(),
                'scheduled_for' => $updated->scheduled_for?->toISOString(),
            ],
            old: $old,
        );

        return $updated;
    }
}
