<?php

namespace App\Modules\Newsletter\Data;

readonly class QueueNewsletterCampaignSendData
{
    public function __construct(
        public ?int $requestedByUserId = null,
    ) {}
}
