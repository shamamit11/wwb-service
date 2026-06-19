<?php

namespace App\Modules\Newsletter\Enums;

enum NewsletterCampaignStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Sending = 'sending';
    case Sent = 'sent';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
}
