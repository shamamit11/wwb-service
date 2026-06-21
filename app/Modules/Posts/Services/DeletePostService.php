<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Repositories\PostRepository;
use App\Support\AuditActivityLogger;
use Illuminate\Support\Facades\DB;

class DeletePostService
{
    public function __construct(
        private readonly PostRepository $posts,
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
                'tag_count' => $post->tags()->count(),
            ];

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
