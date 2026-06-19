<?php

namespace App\Modules\Newsletter\Data;

readonly class UpdateNewsletterSubscriberData
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $email,
        public ?string $name,
        public string $status,
        public ?string $source,
        public ?string $subscribedAt,
        public ?string $unsubscribedAt,
        public ?array $metadata,
    ) {}
}
