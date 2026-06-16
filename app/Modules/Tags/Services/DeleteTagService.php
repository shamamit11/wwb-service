<?php

namespace App\Modules\Tags\Services;

use App\Models\Tag;
use App\Modules\Tags\Repositories\TagRepository;

class DeleteTagService
{
    public function __construct(
        private readonly TagRepository $tags,
    ) {}

    public function handle(Tag $tag): void
    {
        $this->tags->delete($tag);
    }
}
