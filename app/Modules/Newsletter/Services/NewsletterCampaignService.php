<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterCampaign;
use App\Modules\Newsletter\Data\CreateNewsletterCampaignData;
use App\Modules\Newsletter\Data\UpdateNewsletterCampaignData;
use App\Modules\Newsletter\Enums\NewsletterCampaignStatus;
use App\Modules\Newsletter\Repositories\NewsletterCampaignRepository;

class NewsletterCampaignService
{
    public function __construct(
        private readonly NewsletterCampaignRepository $campaigns,
    ) {}

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function createDraft(
        string $title,
        string $subject,
        ?string $previewText = null,
        ?string $contentMarkdown = null,
        ?string $contentHtml = null,
        ?int $createdBy = null,
        ?array $metadata = null,
    ): NewsletterCampaign {
        return $this->campaigns->create(new CreateNewsletterCampaignData(
            title: $title,
            subject: $subject,
            previewText: $previewText,
            contentMarkdown: $contentMarkdown,
            contentHtml: $contentHtml,
            status: NewsletterCampaignStatus::Draft->value,
            scheduledAt: null,
            sentAt: null,
            createdBy: $createdBy,
            metadata: $metadata,
        ));
    }

    public function findCampaign(int $id): ?NewsletterCampaign
    {
        return $this->campaigns->findById($id);
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function updateCampaign(
        NewsletterCampaign $campaign,
        string $title,
        string $subject,
        ?string $previewText = null,
        ?string $contentMarkdown = null,
        ?string $contentHtml = null,
        string $status = NewsletterCampaignStatus::Draft->value,
        ?string $scheduledAt = null,
        ?string $sentAt = null,
        ?array $metadata = null,
    ): NewsletterCampaign {
        return $this->campaigns->update($campaign, new UpdateNewsletterCampaignData(
            title: $title,
            subject: $subject,
            previewText: $previewText,
            contentMarkdown: $contentMarkdown,
            contentHtml: $contentHtml,
            status: $status,
            scheduledAt: $scheduledAt,
            sentAt: $sentAt,
            metadata: $metadata,
        ));
    }

    // TODO: add schedule, cancel, and send lifecycle transitions once execution jobs are introduced.
}
