<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Repositories\PostRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReadAdminPostService
{
    public function __construct(
        private readonly PostRepository $posts,
    ) {}

    public function handle(string $identifier): Post
    {
        $post = ctype_digit($identifier)
            ? $this->posts->findById((int) $identifier)
            : $this->posts->findByUlid($identifier);

        return $post ?? throw (new ModelNotFoundException)->setModel(Post::class, [$identifier]);
    }
}
