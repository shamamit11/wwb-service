<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Data\PostStateTransitionData;
use App\Modules\Posts\Exceptions\InvalidPostStateTransitionException;
use App\Modules\Posts\Repositories\PostRepository;

class UnpublishPostService
{
    public function __construct(
        private readonly PostRepository $posts,
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

        return $this->posts->transition($post, new PostStateTransitionData(
            status: Post::STATUS_UNPUBLISHED,
            publishedAt: null,
            scheduledFor: null,
        ));
    }
}
