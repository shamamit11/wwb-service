<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Data\UpdateNewsletterSubscriberData;
use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;
use App\Modules\Newsletter\Repositories\NewsletterSubscriberRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicUnsubscribeNewsletterService
{
    public function __construct(
        private readonly NewsletterSubscriberRepository $subscribers,
        private readonly NewsletterSubscriberListManager $listManager,
    ) {}

    public function handle(string $unsubscribeToken): NewsletterSubscriber
    {
        $subscriber = $this->subscribers->findByUnsubscribeToken($unsubscribeToken);

        if ($subscriber === null) {
            throw new NotFoundHttpException('Newsletter subscriber not found.');
        }

        $updated = $this->subscribers->update($subscriber, new UpdateNewsletterSubscriberData(
            email: $subscriber->email,
            name: $subscriber->name,
            status: NewsletterSubscriberStatus::Unsubscribed->value,
            source: $subscriber->source,
            subscribedAt: $subscriber->subscribed_at?->toDateTimeString(),
            unsubscribedAt: now()->toDateTimeString(),
            metadata: $subscriber->metadata,
        ));

        return $this->listManager->unsubscribeFromAllLists($updated);
    }
}
