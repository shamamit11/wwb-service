<?php

namespace App\Modules\Newsletter\Data;

readonly class ProcessNewsletterWebhookEventData
{
    public function __construct(
        public string $eventType,
        public ?int $recipientId,
        public ?string $email,
        public ?string $occurredAt,
        public ?string $targetUrl,
    ) {}
}
