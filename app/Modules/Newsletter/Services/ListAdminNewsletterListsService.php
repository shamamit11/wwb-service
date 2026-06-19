<?php

namespace App\Modules\Newsletter\Services;

use App\Modules\Newsletter\Repositories\NewsletterListRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminNewsletterListsService
{
    public function __construct(
        private readonly NewsletterListRepository $lists,
    ) {}

    public function handle(): Collection
    {
        return $this->lists->getAllOrdered();
    }
}
