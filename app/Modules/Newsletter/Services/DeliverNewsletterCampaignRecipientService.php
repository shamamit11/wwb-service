<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterCampaignRecipient;
use App\Modules\Newsletter\Contracts\NewsletterDeliveryProvider;
use App\Modules\Newsletter\Data\UpdateNewsletterCampaignData;
use App\Modules\Newsletter\Data\UpdateNewsletterCampaignRecipientStatusData;
use App\Modules\Newsletter\Enums\NewsletterCampaignStatus;
use App\Modules\Newsletter\Enums\NewsletterRecipientStatus;
use App\Modules\Newsletter\Repositories\NewsletterCampaignRecipientRepository;
use App\Modules\Newsletter\Repositories\NewsletterCampaignRepository;
use App\Modules\Newsletter\Repositories\NewsletterRecipientEventRepository;
use Throwable;

class DeliverNewsletterCampaignRecipientService
{
    public function __construct(
        private readonly NewsletterCampaignRecipientRepository $recipients,
        private readonly NewsletterRecipientEventRepository $events,
        private readonly NewsletterCampaignRepository $campaigns,
        private readonly NewsletterDeliveryProvider $provider,
    ) {}

    public function handle(int $recipientId): void
    {
        $recipient = $this->recipients->findById($recipientId);

        if (! $recipient instanceof NewsletterCampaignRecipient) {
            return;
        }

        if ($recipient->status->value !== NewsletterRecipientStatus::Pending->value) {
            return;
        }

        try {
            $this->provider->send($recipient->campaign, $recipient);

            $this->events->updateStatus($recipient, new UpdateNewsletterCampaignRecipientStatusData(
                status: NewsletterRecipientStatus::Sent->value,
                sentAt: now()->toDateTimeString(),
                errorMessage: null,
            ));
        } catch (Throwable $throwable) {
            $this->events->updateStatus($recipient, new UpdateNewsletterCampaignRecipientStatusData(
                status: NewsletterRecipientStatus::Failed->value,
                failedAt: now()->toDateTimeString(),
                errorMessage: $throwable->getMessage(),
            ));

            throw $throwable;
        } finally {
            $this->finalizeCampaign($recipient->newsletter_campaign_id);
        }
    }

    private function finalizeCampaign(int $campaignId): void
    {
        $campaign = $this->campaigns->findById($campaignId);

        if ($campaign === null) {
            return;
        }

        $recipients = $campaign->recipients;
        $pendingCount = $recipients->filter(fn ($recipient) => $recipient->status->value === NewsletterRecipientStatus::Pending->value)->count();

        if ($pendingCount > 0) {
            return;
        }

        $sentCount = $recipients->filter(fn ($recipient) => $recipient->status->value === NewsletterRecipientStatus::Sent->value)->count();

        $this->campaigns->update($campaign, new UpdateNewsletterCampaignData(
            title: $campaign->title,
            subject: $campaign->subject,
            previewText: $campaign->preview_text,
            contentMarkdown: $campaign->content_markdown,
            contentHtml: $campaign->content_html,
            status: $sentCount > 0 ? NewsletterCampaignStatus::Sent->value : NewsletterCampaignStatus::Failed->value,
            scheduledAt: $campaign->scheduled_at?->toDateTimeString(),
            sentAt: $sentCount > 0 ? now()->toDateTimeString() : $campaign->sent_at?->toDateTimeString(),
            metadata: $campaign->metadata,
        ));
    }
}
