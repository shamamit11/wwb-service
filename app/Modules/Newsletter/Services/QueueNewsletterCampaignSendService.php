<?php

namespace App\Modules\Newsletter\Services;

use App\Jobs\Newsletter\SendNewsletterCampaignJob;
use App\Models\NewsletterCampaign;
use App\Modules\Newsletter\Enums\NewsletterCampaignStatus;
use App\Modules\Newsletter\Exceptions\NewsletterCampaignSendNotAllowedException;

class QueueNewsletterCampaignSendService
{
    public function __construct(
        private readonly NewsletterCampaignService $campaigns,
    ) {}

    public function handle(NewsletterCampaign $campaign): NewsletterCampaign
    {
        if ($campaign->status === NewsletterCampaignStatus::Sent) {
            throw new NewsletterCampaignSendNotAllowedException(
                campaignStatus: $campaign->status->value,
                message: 'Newsletter campaign has already been sent.',
            );
        }

        if ($campaign->recipients()->count() < 1) {
            throw new NewsletterCampaignSendNotAllowedException(
                campaignStatus: $campaign->status->value,
                message: 'Newsletter campaign must have staged recipients before sending.',
            );
        }

        if (
            blank($campaign->content_html)
            && blank($campaign->content_markdown)
        ) {
            throw new NewsletterCampaignSendNotAllowedException(
                campaignStatus: $campaign->status->value,
                message: 'Newsletter campaign must have content before sending.',
            );
        }

        $updated = $this->campaigns->updateCampaign(
            campaign: $campaign,
            title: $campaign->title,
            subject: $campaign->subject,
            previewText: $campaign->preview_text,
            contentMarkdown: $campaign->content_markdown,
            contentHtml: $campaign->content_html,
            status: NewsletterCampaignStatus::Sending->value,
            scheduledAt: $campaign->scheduled_at?->toDateTimeString(),
            sentAt: $campaign->sent_at?->toDateTimeString(),
            metadata: $campaign->metadata,
        );

        SendNewsletterCampaignJob::dispatch((int) $updated->id);

        return $updated;
    }
}
