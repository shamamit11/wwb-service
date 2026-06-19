<?php

namespace App\Modules\Newsletter\Data;

readonly class UpdateNewsletterCampaignData
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $title,
        public string $subject,
        public ?string $previewText,
        public ?string $contentMarkdown,
        public ?string $contentHtml,
        public string $status,
        public ?string $scheduledAt,
        public ?string $sentAt,
        public ?array $metadata,
    ) {}
}
