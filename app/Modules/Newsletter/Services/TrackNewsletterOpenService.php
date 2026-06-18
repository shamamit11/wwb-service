<?php

namespace App\Modules\Newsletter\Services;

use App\Modules\Newsletter\Data\UpdateNewsletterCampaignRecipientStatusData;
use App\Modules\Newsletter\Enums\NewsletterRecipientStatus;
use App\Modules\Newsletter\Repositories\NewsletterRecipientEventRepository;

class TrackNewsletterOpenService
{
    public function __construct(
        private readonly NewsletterRecipientEventRepository $events,
    ) {}

    public function handle(int $recipientId): void
    {
        $recipient = $this->events->findById($recipientId);

        if ($recipient === null) {
            return;
        }

        $this->events->updateStatus($recipient, new UpdateNewsletterCampaignRecipientStatusData(
            status: $recipient->status->value === NewsletterRecipientStatus::Pending->value
                ? NewsletterRecipientStatus::Sent->value
                : $recipient->status->value,
            sentAt: $recipient->sent_at?->toDateTimeString() ?? now()->toDateTimeString(),
            failedAt: $recipient->failed_at?->toDateTimeString(),
            errorMessage: $recipient->error_message,
            openedAt: $recipient->opened_at?->toDateTimeString() ?? now()->toDateTimeString(),
            clickedAt: $recipient->clicked_at?->toDateTimeString(),
            unsubscribedAt: $recipient->unsubscribed_at?->toDateTimeString(),
        ));
    }
}
