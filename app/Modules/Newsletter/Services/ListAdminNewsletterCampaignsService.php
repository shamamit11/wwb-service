<?php

namespace App\Modules\Newsletter\Services;

use App\Modules\Newsletter\Repositories\NewsletterCampaignRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminNewsletterCampaignsService
{
    public function __construct(
        private readonly NewsletterCampaignRepository $campaigns,
    ) {}

    public function handle(): Collection
    {
        return $this->campaigns->getAllOrdered();
    }
}
