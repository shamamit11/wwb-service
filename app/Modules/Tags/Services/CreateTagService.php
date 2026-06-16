<?php

namespace App\Modules\Tags\Services;

use App\Models\Tag;
use App\Modules\Tags\Data\CreateTagData;
use App\Modules\Tags\Repositories\TagRepository;

class CreateTagService
{
    public function __construct(
        private readonly TagRepository $tags,
        private readonly TagSlugResolver $slugResolver,
    ) {}

    public function handle(CreateTagData $data): Tag
    {
        return $this->tags->create(new CreateTagData(
            name: $data->name,
            slug: $this->slugResolver->resolve($data->name, $data->slug),
            description: $data->description,
            isActive: $data->isActive,
        ));
    }
}
