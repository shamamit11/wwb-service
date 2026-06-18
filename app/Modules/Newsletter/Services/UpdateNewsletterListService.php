<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterList;
use App\Modules\Newsletter\Data\UpdateNewsletterListData;
use App\Modules\Newsletter\Repositories\NewsletterListRepository;

class UpdateNewsletterListService
{
    public function __construct(
        private readonly NewsletterListRepository $lists,
        private readonly NewsletterListSlugResolver $slugResolver,
    ) {}

    public function handle(NewsletterList $list, UpdateNewsletterListData $data): NewsletterList
    {
        return $this->lists->update($list, new UpdateNewsletterListData(
            name: $data->name,
            slug: $this->slugResolver->resolve($data->name, $data->slug, $list->id),
            description: $data->description,
            status: $data->status,
        ));
    }
}
