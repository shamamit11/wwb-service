<?php

namespace App\Modules\Newsletter\Services;

use Illuminate\Support\Str;

class NewsletterTokenService
{
    public function generateUnsubscribeToken(): string
    {
        return Str::random(64);
    }
}
