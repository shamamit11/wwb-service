<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Data\UpdateNewsletterSubscriberData;
use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;
use App\Modules\Newsletter\Repositories\NewsletterSubscriberRepository;

class UpdateNewsletterSubscriberService
{
    public function __construct(
        private readonly NewsletterSubscriberRepository $subscribers,
    ) {}

    public function handle(NewsletterSubscriber $subscriber, UpdateNewsletterSubscriberData $data): NewsletterSubscriber
    {
        $subscribedAt = $subscriber->subscribed_at?->toDateTimeString();
        $unsubscribedAt = $subscriber->unsubscribed_at?->toDateTimeString();

        if ($data->status === NewsletterSubscriberStatus::Unsubscribed->value) {
            $unsubscribedAt = $unsubscribedAt ?? now()->toDateTimeString();
        }

        if ($data->status === NewsletterSubscriberStatus::Active->value) {
            $subscribedAt = $subscribedAt ?? now()->toDateTimeString();
            $unsubscribedAt = null;
        }

        return $this->subscribers->update($subscriber, new UpdateNewsletterSubscriberData(
            email: $data->email,
            name: $data->name,
            status: $data->status,
            source: $data->source,
            subscribedAt: $subscribedAt,
            unsubscribedAt: $unsubscribedAt,
            metadata: $data->metadata,
        ));
    }
}
