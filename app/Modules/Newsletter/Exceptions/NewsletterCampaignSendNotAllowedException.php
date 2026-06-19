<?php

namespace App\Modules\Newsletter\Exceptions;

use RuntimeException;

class NewsletterCampaignSendNotAllowedException extends RuntimeException
{
    public function __construct(
        public readonly string $campaignStatus,
        string $message,
    ) {
        parent::__construct($message);
    }
}
