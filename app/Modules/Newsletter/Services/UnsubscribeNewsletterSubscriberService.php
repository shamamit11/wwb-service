<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Data\UpdateNewsletterSubscriberData;
use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;

class UnsubscribeNewsletterSubscriberService
{
    public function __construct(
        private readonly UpdateNewsletterSubscriberService $updater,
    ) {}

    public function handle(NewsletterSubscriber $subscriber): NewsletterSubscriber
    {
        return $this->updater->handle($subscriber, new UpdateNewsletterSubscriberData(
            email: $subscriber->email,
            name: $subscriber->name,
            status: NewsletterSubscriberStatus::Unsubscribed->value,
            source: $subscriber->source,
            subscribedAt: $subscriber->subscribed_at?->toDateTimeString(),
            unsubscribedAt: now()->toDateTimeString(),
            metadata: $subscriber->metadata,
        ));
    }
}
