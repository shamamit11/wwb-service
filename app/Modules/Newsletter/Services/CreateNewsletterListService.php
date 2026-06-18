<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterList;
use App\Modules\Newsletter\Data\CreateNewsletterListData;
use App\Modules\Newsletter\Repositories\NewsletterListRepository;

class CreateNewsletterListService
{
    public function __construct(
        private readonly NewsletterListRepository $lists,
        private readonly NewsletterListSlugResolver $slugResolver,
    ) {}

    public function handle(CreateNewsletterListData $data): NewsletterList
    {
        return $this->lists->create(new CreateNewsletterListData(
            name: $data->name,
            slug: $this->slugResolver->resolve($data->name, $data->slug),
            description: $data->description,
            status: $data->status,
        ));
    }
}
