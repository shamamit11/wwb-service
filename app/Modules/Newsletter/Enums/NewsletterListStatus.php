<?php

namespace App\Modules\Newsletter\Enums;

enum NewsletterListStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
