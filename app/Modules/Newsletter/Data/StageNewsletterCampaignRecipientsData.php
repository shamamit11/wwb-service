<?php

namespace App\Modules\Newsletter\Data;

readonly class StageNewsletterCampaignRecipientsData
{
    /**
     * @param  array<int, int>  $subscriberIds
     * @param  array<int, int>  $listIds
     */
    public function __construct(
        public array $subscriberIds,
        public array $listIds,
        public bool $includeAllActiveSubscribers,
    ) {}
}
