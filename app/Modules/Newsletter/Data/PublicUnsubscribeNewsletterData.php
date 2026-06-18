<?php

namespace App\Modules\Newsletter\Data;

readonly class PublicUnsubscribeNewsletterData
{
    public function __construct(
        public string $unsubscribeToken,
    ) {}
}
