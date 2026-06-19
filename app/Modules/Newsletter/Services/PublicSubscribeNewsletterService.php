<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Data\PublicSubscribeNewsletterData;
use App\Modules\Newsletter\Data\UpdateNewsletterSubscriberData;
use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;
use App\Modules\Newsletter\Repositories\NewsletterSubscriberRepository;

class PublicSubscribeNewsletterService
{
    public function __construct(
        private readonly NewsletterSubscriptionService $subscriptions,
        private readonly NewsletterSubscriberRepository $subscribers,
        private readonly NewsletterSubscriberListManager $listManager,
    ) {}

    public function handle(PublicSubscribeNewsletterData $data): NewsletterSubscriber
    {
        $existing = $this->subscribers->findByEmail($data->email);

        if ($existing === null) {
            $subscriber = $this->subscriptions->createActiveSubscriber(
                email: $data->email,
                name: $data->name,
                source: $data->source ?? 'public',
                metadata: $data->metadata,
            );

            return $this->listManager->subscribeToLists($subscriber, $data->listIds);
        }

        $updated = $this->subscribers->update($existing, new UpdateNewsletterSubscriberData(
            email: $data->email,
            name: $data->name ?? $existing->name,
            status: NewsletterSubscriberStatus::Active->value,
            source: $data->source ?? $existing->source,
            subscribedAt: now()->toDateTimeString(),
            unsubscribedAt: null,
            metadata: $data->metadata ?? $existing->metadata,
        ));

        return $this->listManager->subscribeToLists($updated, $data->listIds);
    }
}
