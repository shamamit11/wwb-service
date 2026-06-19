<?php

namespace App\Modules\Newsletter\Contracts;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterCampaignRecipient;
use App\Modules\Newsletter\DTO\NewsletterDeliveryResult;

interface NewsletterDeliveryProvider
{
    public function send(NewsletterCampaign $campaign, NewsletterCampaignRecipient $recipient): NewsletterDeliveryResult;
}
