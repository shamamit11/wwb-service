<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterList;
use App\Modules\Newsletter\Repositories\NewsletterListRepository;

class DeleteNewsletterListService
{
    public function __construct(
        private readonly NewsletterListRepository $lists,
    ) {}

    public function handle(NewsletterList $list): void
    {
        $this->lists->delete($list);
    }
}
