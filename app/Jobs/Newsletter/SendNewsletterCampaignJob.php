<?php

namespace App\Jobs\Newsletter;

use App\Modules\Newsletter\Services\SendNewsletterCampaignService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNewsletterCampaignJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $campaignId,
    ) {
        $this->onQueue((string) config('newsletter.queues.campaigns', 'newsletter'));
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(SendNewsletterCampaignService $service): void
    {
        $service->handle($this->campaignId);
    }
}
