<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Repositories\PostBlockRepository;
use App\Modules\Posts\Repositories\PostRepository;
use App\Support\AuditActivityLogger;
use Illuminate\Support\Facades\DB;

class DeletePostService
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly PostBlockRepository $blocks,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(Post $post): void
    {
        DB::transaction(function () use ($post): void {
            $attributes = [
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status,
                'visibility' => $post->visibility,
            ];
            $context = [
                'block_count' => $post->blocks()->count(),
                'tag_count' => $post->tags()->count(),
            ];

            $this->blocks->deleteForPost($post);
            $this->posts->delete($post);

            $this->audit->log(
                logName: 'content',
                description: 'post.deleted',
                event: 'deleted',
                subject: $post,
                attributes: $attributes,
                context: $context,
            );
        });
    }
}
