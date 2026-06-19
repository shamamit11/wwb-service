<?php

namespace App\Modules\Newsletter\Repositories;

use App\Models\NewsletterCampaignRecipient;
use App\Modules\Newsletter\Data\CreateNewsletterCampaignRecipientData;
use Illuminate\Database\Eloquent\Collection;

class EloquentNewsletterCampaignRecipientRepository implements NewsletterCampaignRecipientRepository
{
    public function create(CreateNewsletterCampaignRecipientData $data): NewsletterCampaignRecipient
    {
        return NewsletterCampaignRecipient::query()->create([
            'newsletter_campaign_id' => $data->newsletterCampaignId,
            'newsletter_subscriber_id' => $data->newsletterSubscriberId,
            'email' => $data->email,
            'status' => $data->status,
            'sent_at' => $data->sentAt,
            'failed_at' => $data->failedAt,
            'error_message' => $data->errorMessage,
            'opened_at' => $data->openedAt,
            'clicked_at' => $data->clickedAt,
            'unsubscribed_at' => $data->unsubscribedAt,
        ])->refresh()->load(['campaign', 'subscriber']);
    }

    public function findById(int $id): ?NewsletterCampaignRecipient
    {
        return NewsletterCampaignRecipient::query()
            ->with(['campaign', 'subscriber'])
            ->find($id);
    }

    /**
     * @return Collection<int, NewsletterCampaignRecipient>
     */
    public function findByCampaignId(int $campaignId): Collection
    {
        return NewsletterCampaignRecipient::query()
            ->with(['campaign', 'subscriber'])
            ->where('newsletter_campaign_id', $campaignId)
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, NewsletterCampaignRecipient>
     */
    public function findPendingByCampaignId(int $campaignId): Collection
    {
        return NewsletterCampaignRecipient::query()
            ->with(['campaign', 'subscriber'])
            ->where('newsletter_campaign_id', $campaignId)
            ->where('status', 'pending')
            ->orderBy('id')
            ->get();
    }
}
