<?php

namespace App\Modules\Newsletter\Repositories;

use App\Models\NewsletterCampaignRecipient;
use App\Modules\Newsletter\Data\CreateNewsletterCampaignRecipientData;
use Illuminate\Database\Eloquent\Collection;

interface NewsletterCampaignRecipientRepository
{
    public function create(CreateNewsletterCampaignRecipientData $data): NewsletterCampaignRecipient;

    public function findById(int $id): ?NewsletterCampaignRecipient;

    /**
     * @return Collection<int, NewsletterCampaignRecipient>
     */
    public function findByCampaignId(int $campaignId): Collection;

    /**
     * @return Collection<int, NewsletterCampaignRecipient>
     */
    public function findPendingByCampaignId(int $campaignId): Collection;
}
