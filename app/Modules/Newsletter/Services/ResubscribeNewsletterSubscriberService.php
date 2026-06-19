<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Data\UpdateNewsletterSubscriberData;
use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;

class ResubscribeNewsletterSubscriberService
{
    public function __construct(
        private readonly UpdateNewsletterSubscriberService $updater,
    ) {}

    public function handle(NewsletterSubscriber $subscriber): NewsletterSubscriber
    {
        return $this->updater->handle($subscriber, new UpdateNewsletterSubscriberData(
            email: $subscriber->email,
            name: $subscriber->name,
            status: NewsletterSubscriberStatus::Active->value,
            source: $subscriber->source,
            subscribedAt: now()->toDateTimeString(),
            unsubscribedAt: null,
            metadata: $subscriber->metadata,
        ));
    }
}
