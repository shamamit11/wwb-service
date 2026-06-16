<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Repositories\PostBlockRepository;
use App\Modules\Posts\Repositories\PostRepository;
use Illuminate\Support\Facades\DB;

class DeletePostService
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly PostBlockRepository $blocks,
    ) {}

    public function handle(Post $post): void
    {
        DB::transaction(function () use ($post): void {
            $this->blocks->deleteForPost($post);
            $this->posts->delete($post);
        });
    }
}
