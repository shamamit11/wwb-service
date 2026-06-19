<?php

namespace App\Modules\Newsletter\DTO;

readonly class NewsletterDeliveryResult
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $provider,
        public ?array $metadata = null,
    ) {}
}
