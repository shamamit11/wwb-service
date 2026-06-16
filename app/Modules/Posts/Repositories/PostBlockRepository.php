<?php

namespace App\Modules\Posts\Repositories;

use App\Models\Post;
use App\Models\PostBlock;
use App\Modules\Posts\Data\CreatePostBlockData;
use Illuminate\Database\Eloquent\Collection;

interface PostBlockRepository
{
    /**
     * @param  list<CreatePostBlockData>  $blocks
     */
    public function replaceForPost(Post $post, array $blocks): void;

    public function deleteForPost(Post $post): void;

    /**
     * @return Collection<int, PostBlock>
     */
    public function getOrderedForPost(Post $post): Collection;
}
