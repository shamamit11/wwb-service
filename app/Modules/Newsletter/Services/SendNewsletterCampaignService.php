<?php

namespace App\Modules\Newsletter\Services;

use App\Jobs\Newsletter\DeliverNewsletterCampaignRecipientJob;
use App\Models\NewsletterCampaign;
use App\Modules\Newsletter\Repositories\NewsletterCampaignRecipientRepository;
use App\Modules\Newsletter\Repositories\NewsletterCampaignRepository;
use RuntimeException;

class SendNewsletterCampaignService
{
    public function __construct(
        private readonly NewsletterCampaignRepository $campaigns,
        private readonly NewsletterCampaignRecipientRepository $recipients,
    ) {}

    public function handle(int $campaignId): void
    {
        $campaign = $this->campaigns->findById($campaignId);

        if (! $campaign instanceof NewsletterCampaign) {
            throw new RuntimeException("Newsletter campaign [{$campaignId}] could not be found.");
        }

        foreach ($this->recipients->findPendingByCampaignId($campaignId) as $recipient) {
            DeliverNewsletterCampaignRecipientJob::dispatch((int) $recipient->id);
        }
    }
}
