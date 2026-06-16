<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Data\PostStateTransitionData;
use App\Modules\Posts\Exceptions\InvalidPostStateTransitionException;
use App\Modules\Posts\Repositories\PostRepository;

class PublishPostService
{
    public function __construct(
        private readonly PostRepository $posts,
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

        return $this->posts->transition($post, new PostStateTransitionData(
            status: Post::STATUS_PUBLISHED,
            publishedAt: now()->toDateTimeString(),
            scheduledFor: null,
        ));
    }
}
