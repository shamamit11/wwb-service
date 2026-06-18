<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterCampaign;
use App\Modules\Newsletter\Data\UpdateNewsletterCampaignData;

class UpdateNewsletterCampaignService
{
    public function __construct(
        private readonly NewsletterCampaignService $campaigns,
    ) {}

    public function handle(NewsletterCampaign $campaign, UpdateNewsletterCampaignData $data): NewsletterCampaign
    {
        return $this->campaigns->updateCampaign(
            campaign: $campaign,
            title: $data->title,
            subject: $data->subject,
            previewText: $data->previewText,
            contentMarkdown: $data->contentMarkdown,
            contentHtml: $data->contentHtml,
            status: $data->status,
            scheduledAt: $data->scheduledAt,
            sentAt: $data->sentAt,
            metadata: $data->metadata,
        );
    }
}
