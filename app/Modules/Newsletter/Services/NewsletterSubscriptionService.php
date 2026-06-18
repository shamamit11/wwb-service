<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Data\CreateNewsletterSubscriberData;
use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;
use App\Modules\Newsletter\Repositories\NewsletterSubscriberRepository;

class NewsletterSubscriptionService
{
    public function __construct(
        private readonly NewsletterSubscriberRepository $subscribers,
        private readonly NewsletterTokenService $tokenService,
    ) {}

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function createActiveSubscriber(
        string $email,
        ?string $name = null,
        ?string $source = null,
        ?array $metadata = null,
    ): NewsletterSubscriber {
        return $this->subscribers->create(new CreateNewsletterSubscriberData(
            email: $email,
            name: $name,
            status: NewsletterSubscriberStatus::Active->value,
            source: $source,
            subscribedAt: now()->toDateTimeString(),
            unsubscribedAt: null,
            unsubscribeToken: $this->tokenService->generateUnsubscribeToken(),
            metadata: $metadata,
        ));
    }

    public function findSubscriberByEmail(string $email): ?NewsletterSubscriber
    {
        return $this->subscribers->findByEmail($email);
    }

    // TODO: add list-aware subscribe, unsubscribe, and resubscribe workflows in later newsletter stories.
}
