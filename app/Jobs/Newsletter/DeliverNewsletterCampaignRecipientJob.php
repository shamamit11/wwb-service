<?php

namespace App\Jobs\Newsletter;

use App\Modules\Newsletter\Services\DeliverNewsletterCampaignRecipientService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeliverNewsletterCampaignRecipientJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $recipientId,
    ) {
        $this->onQueue((string) config('newsletter.queues.recipients', 'newsletter'));
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(DeliverNewsletterCampaignRecipientService $service): void
    {
        $service->handle($this->recipientId);
    }
}
