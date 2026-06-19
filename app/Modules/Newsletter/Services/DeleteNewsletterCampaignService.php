<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterCampaign;
use App\Modules\Newsletter\Repositories\NewsletterCampaignRepository;

class DeleteNewsletterCampaignService
{
    public function __construct(
        private readonly NewsletterCampaignRepository $campaigns,
    ) {}

    public function handle(NewsletterCampaign $campaign): void
    {
        $this->campaigns->delete($campaign);
    }
}
