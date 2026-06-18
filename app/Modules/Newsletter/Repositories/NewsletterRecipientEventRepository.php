<?php

namespace App\Modules\Newsletter\Repositories;

use App\Models\NewsletterCampaignRecipient;
use App\Modules\Newsletter\Data\UpdateNewsletterCampaignRecipientStatusData;

interface NewsletterRecipientEventRepository
{
    public function findById(int $id): ?NewsletterCampaignRecipient;

    public function findLatestByEmail(string $email): ?NewsletterCampaignRecipient;

    public function updateStatus(
        NewsletterCampaignRecipient $recipient,
        UpdateNewsletterCampaignRecipientStatusData $data,
    ): NewsletterCampaignRecipient;
}
