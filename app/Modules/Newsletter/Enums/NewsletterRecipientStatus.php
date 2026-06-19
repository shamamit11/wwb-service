<?php

namespace App\Modules\Newsletter\Enums;

enum NewsletterRecipientStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
    case Skipped = 'skipped';
    case Unsubscribed = 'unsubscribed';
}
