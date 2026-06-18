<?php

namespace App\Modules\Newsletter\Data;

readonly class CreateNewsletterCampaignRecipientData
{
    public function __construct(
        public int $newsletterCampaignId,
        public int $newsletterSubscriberId,
        public string $email,
        public string $status,
        public ?string $sentAt = null,
        public ?string $failedAt = null,
        public ?string $errorMessage = null,
        public ?string $openedAt = null,
        public ?string $clickedAt = null,
        public ?string $unsubscribedAt = null,
    ) {}
}
