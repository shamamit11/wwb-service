<?php

namespace App\Modules\Newsletter\Data;

readonly class UpdateNewsletterCampaignRecipientStatusData
{
    public function __construct(
        public string $status,
        public ?string $sentAt = null,
        public ?string $failedAt = null,
        public ?string $errorMessage = null,
        public ?string $openedAt = null,
        public ?string $clickedAt = null,
        public ?string $unsubscribedAt = null,
    ) {}
}
