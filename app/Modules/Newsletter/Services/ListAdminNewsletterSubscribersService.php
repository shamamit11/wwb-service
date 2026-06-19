<?php

namespace App\Modules\Newsletter\Services;

use App\Modules\Newsletter\Repositories\NewsletterSubscriberRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminNewsletterSubscribersService
{
    public function __construct(
        private readonly NewsletterSubscriberRepository $subscribers,
    ) {}

    public function handle(): Collection
    {
        return $this->subscribers->getAllOrdered();
    }
}
