<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterCampaign;
use App\Modules\Newsletter\Data\StageNewsletterCampaignRecipientsData;
use App\Modules\Newsletter\Repositories\NewsletterSubscriberRepository;
use Illuminate\Database\Eloquent\Collection;

class StageNewsletterCampaignRecipientsService
{
    public function __construct(
        private readonly NewsletterSubscriberRepository $subscribers,
        private readonly NewsletterDeliveryService $delivery,
    ) {}

    public function handle(NewsletterCampaign $campaign, StageNewsletterCampaignRecipientsData $data): Collection
    {
        $subscriberMap = [];

        if ($data->includeAllActiveSubscribers) {
            foreach ($this->subscribers->findAllActive() as $subscriber) {
                $subscriberMap[$subscriber->id] = $subscriber;
            }
        }

        if ($data->subscriberIds !== []) {
            foreach ($this->subscribers->findActiveByIds($data->subscriberIds) as $subscriber) {
                $subscriberMap[$subscriber->id] = $subscriber;
            }
        }

        if ($data->listIds !== []) {
            foreach ($this->subscribers->findActiveByListIds($data->listIds) as $subscriber) {
                $subscriberMap[$subscriber->id] = $subscriber;
            }
        }

        $this->delivery->stageRecipients($campaign, array_values($subscriberMap));

        return $campaign->fresh('recipients.subscriber')?->recipients ?? collect();
    }
}
