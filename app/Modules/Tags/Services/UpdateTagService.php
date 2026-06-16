<?php

namespace App\Modules\Tags\Services;

use App\Models\Tag;
use App\Modules\Tags\Data\UpdateTagData;
use App\Modules\Tags\Repositories\TagRepository;

class UpdateTagService
{
    public function __construct(
        private readonly TagRepository $tags,
        private readonly TagSlugResolver $slugResolver,
    ) {}

    public function handle(Tag $tag, UpdateTagData $data): Tag
    {
        return $this->tags->update($tag, new UpdateTagData(
            name: $data->name,
            slug: $this->slugResolver->resolve($data->name, $data->slug, $tag->id),
            description: $data->description,
            isActive: $data->isActive,
        ));
    }
}
