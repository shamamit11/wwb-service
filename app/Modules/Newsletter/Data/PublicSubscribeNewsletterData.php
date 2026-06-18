<?php

namespace App\Modules\Newsletter\Data;

readonly class PublicSubscribeNewsletterData
{
    /**
     * @param  array<int, int>  $listIds
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $email,
        public ?string $name,
        public ?string $source,
        public array $listIds,
        public ?array $metadata,
    ) {}
}
