<?php

namespace App\Modules\Newsletter\Services;

use App\Modules\Newsletter\Data\ProcessNewsletterWebhookEventData;
use App\Modules\Newsletter\Data\UpdateNewsletterCampaignRecipientStatusData;
use App\Modules\Newsletter\Data\UpdateNewsletterSubscriberData;
use App\Modules\Newsletter\Enums\NewsletterRecipientStatus;
use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;
use App\Modules\Newsletter\Repositories\NewsletterRecipientEventRepository;
use App\Modules\Newsletter\Repositories\NewsletterSubscriberRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProcessNewsletterWebhookEventService
{
    public function __construct(
        private readonly NewsletterRecipientEventRepository $events,
        private readonly NewsletterSubscriberRepository $subscribers,
        private readonly NewsletterSubscriberListManager $listManager,
    ) {}

    public function handle(ProcessNewsletterWebhookEventData $data): void
    {
        $recipient = $data->recipientId !== null
            ? $this->events->findById($data->recipientId)
            : ($data->email !== null ? $this->events->findLatestByEmail($data->email) : null);

        if ($recipient === null) {
            throw new NotFoundHttpException('Newsletter recipient not found.');
        }

        $occurredAt = $data->occurredAt ?? now()->toDateTimeString();

        match ($data->eventType) {
            'delivered' => $this->events->updateStatus($recipient, new UpdateNewsletterCampaignRecipientStatusData(
                status: NewsletterRecipientStatus::Sent->value,
                sentAt: $recipient->sent_at?->toDateTimeString() ?? $occurredAt,
                failedAt: $recipient->failed_at?->toDateTimeString(),
                errorMessage: null,
                openedAt: $recipient->opened_at?->toDateTimeString(),
                clickedAt: $recipient->clicked_at?->toDateTimeString(),
                unsubscribedAt: $recipient->unsubscribed_at?->toDateTimeString(),
            )),
            'opened' => $this->events->updateStatus($recipient, new UpdateNewsletterCampaignRecipientStatusData(
                status: NewsletterRecipientStatus::Sent->value,
                sentAt: $recipient->sent_at?->toDateTimeString() ?? $occurredAt,
                openedAt: $recipient->opened_at?->toDateTimeString() ?? $occurredAt,
                clickedAt: $recipient->clicked_at?->toDateTimeString(),
                failedAt: $recipient->failed_at?->toDateTimeString(),
                errorMessage: $recipient->error_message,
                unsubscribedAt: $recipient->unsubscribed_at?->toDateTimeString(),
            )),
            'clicked' => $this->events->updateStatus($recipient, new UpdateNewsletterCampaignRecipientStatusData(
                status: NewsletterRecipientStatus::Sent->value,
                sentAt: $recipient->sent_at?->toDateTimeString() ?? $occurredAt,
                openedAt: $recipient->opened_at?->toDateTimeString(),
                clickedAt: $recipient->clicked_at?->toDateTimeString() ?? $occurredAt,
                failedAt: $recipient->failed_at?->toDateTimeString(),
                errorMessage: $recipient->error_message,
                unsubscribedAt: $recipient->unsubscribed_at?->toDateTimeString(),
            )),
            'bounced' => $this->markSubscriberAndRecipient(
                recipientStatus: NewsletterRecipientStatus::Failed->value,
                subscriberStatus: NewsletterSubscriberStatus::Bounced->value,
                recipientId: (int) $recipient->id,
                subscriberId: (int) $recipient->newsletter_subscriber_id,
                occurredAt: $occurredAt,
            ),
            'complained' => $this->markSubscriberAndRecipient(
                recipientStatus: NewsletterRecipientStatus::Failed->value,
                subscriberStatus: NewsletterSubscriberStatus::Complained->value,
                recipientId: (int) $recipient->id,
                subscriberId: (int) $recipient->newsletter_subscriber_id,
                occurredAt: $occurredAt,
            ),
            'unsubscribed' => $this->markSubscriberAndRecipient(
                recipientStatus: NewsletterRecipientStatus::Unsubscribed->value,
                subscriberStatus: NewsletterSubscriberStatus::Unsubscribed->value,
                recipientId: (int) $recipient->id,
                subscriberId: (int) $recipient->newsletter_subscriber_id,
                occurredAt: $occurredAt,
            ),
            default => null,
        };
    }

    private function markSubscriberAndRecipient(
        string $recipientStatus,
        string $subscriberStatus,
        int $recipientId,
        int $subscriberId,
        string $occurredAt,
    ): void {
        $recipient = $this->events->findById($recipientId);
        $subscriber = $this->subscribers->findById($subscriberId);

        if ($recipient === null || $subscriber === null) {
            throw new NotFoundHttpException('Newsletter event target not found.');
        }

        $this->events->updateStatus($recipient, new UpdateNewsletterCampaignRecipientStatusData(
            status: $recipientStatus,
            sentAt: $recipient->sent_at?->toDateTimeString(),
            failedAt: $recipientStatus === NewsletterRecipientStatus::Failed->value ? $occurredAt : $recipient->failed_at?->toDateTimeString(),
            errorMessage: $recipient->error_message,
            openedAt: $recipient->opened_at?->toDateTimeString(),
            clickedAt: $recipient->clicked_at?->toDateTimeString(),
            unsubscribedAt: $recipientStatus === NewsletterRecipientStatus::Unsubscribed->value ? $occurredAt : $recipient->unsubscribed_at?->toDateTimeString(),
        ));

        $updatedSubscriber = $this->subscribers->update($subscriber, new UpdateNewsletterSubscriberData(
            email: $subscriber->email,
            name: $subscriber->name,
            status: $subscriberStatus,
            source: $subscriber->source,
            subscribedAt: $subscriber->subscribed_at?->toDateTimeString(),
            unsubscribedAt: $subscriberStatus === NewsletterSubscriberStatus::Unsubscribed->value ? $occurredAt : $subscriber->unsubscribed_at?->toDateTimeString(),
            metadata: $subscriber->metadata,
        ));

        if ($subscriberStatus === NewsletterSubscriberStatus::Unsubscribed->value) {
            $this->listManager->unsubscribeFromAllLists($updatedSubscriber);
        }
    }
}
