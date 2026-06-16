<?php

namespace App\Modules\Tags\Services;

use App\Modules\Tags\Repositories\TagRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminTagsService
{
    public function __construct(
        private readonly TagRepository $tags,
    ) {}

    public function handle(): Collection
    {
        return $this->tags->getAllOrdered();
    }
}
