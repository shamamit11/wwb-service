<?php

namespace App\Modules\Seo\Services;

use App\Modules\Posts\Repositories\PostRepository;
use Illuminate\Database\Eloquent\Collection;

class ListRssFeedEntriesService
{
    public function __construct(
        private readonly PostRepository $posts,
    ) {}

    public function handle(): Collection
    {
        return $this->posts->getPublishedOrdered();
    }
}
