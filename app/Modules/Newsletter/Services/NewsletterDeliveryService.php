<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Data\CreateNewsletterCampaignRecipientData;
use App\Modules\Newsletter\Enums\NewsletterRecipientStatus;
use App\Modules\Newsletter\Repositories\NewsletterCampaignRecipientRepository;

class NewsletterDeliveryService
{
    public function __construct(
        private readonly NewsletterCampaignRecipientRepository $recipients,
    ) {}

    /**
     * @param  iterable<NewsletterSubscriber>  $subscribers
     * @return array<int, \App\Models\NewsletterCampaignRecipient>
     */
    public function stageRecipients(NewsletterCampaign $campaign, iterable $subscribers): array
    {
        $existingSubscriberIds = $this->recipients->findByCampaignId($campaign->id)
            ->pluck('newsletter_subscriber_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $stagedRecipients = [];

        foreach ($subscribers as $subscriber) {
            if (in_array((int) $subscriber->id, $existingSubscriberIds, true)) {
                continue;
            }

            $stagedRecipients[] = $this->recipients->create(new CreateNewsletterCampaignRecipientData(
                newsletterCampaignId: $campaign->id,
                newsletterSubscriberId: $subscriber->id,
                email: $subscriber->email,
                status: NewsletterRecipientStatus::Pending->value,
            ));
        }

        return $stagedRecipients;
    }

    // TODO: add provider dispatch, bounce handling, and engagement tracking updates in later tasks.
}
