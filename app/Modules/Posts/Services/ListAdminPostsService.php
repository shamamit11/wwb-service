<?php

namespace App\Modules\Posts\Services;

use App\Modules\Posts\Data\PostFiltersData;
use App\Modules\Posts\Repositories\PostRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminPostsService
{
    public function __construct(
        private readonly PostRepository $posts,
    ) {}

    public function handle(PostFiltersData $filters): Collection
    {
        return $this->posts->searchAdmin($filters);
    }
}
