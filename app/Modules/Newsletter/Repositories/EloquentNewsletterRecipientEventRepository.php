<?php

namespace App\Modules\Newsletter\Repositories;

use App\Models\NewsletterCampaignRecipient;
use App\Modules\Newsletter\Data\UpdateNewsletterCampaignRecipientStatusData;

class EloquentNewsletterRecipientEventRepository implements NewsletterRecipientEventRepository
{
    public function findById(int $id): ?NewsletterCampaignRecipient
    {
        return NewsletterCampaignRecipient::query()
            ->with(['subscriber', 'campaign'])
            ->find($id);
    }

    public function findLatestByEmail(string $email): ?NewsletterCampaignRecipient
    {
        return NewsletterCampaignRecipient::query()
            ->with(['subscriber', 'campaign'])
            ->where('email', $email)
            ->latest('id')
            ->first();
    }

    public function updateStatus(
        NewsletterCampaignRecipient $recipient,
        UpdateNewsletterCampaignRecipientStatusData $data,
    ): NewsletterCampaignRecipient {
        $recipient->update([
            'status' => $data->status,
            'sent_at' => $data->sentAt,
            'failed_at' => $data->failedAt,
            'error_message' => $data->errorMessage,
            'opened_at' => $data->openedAt,
            'clicked_at' => $data->clickedAt,
            'unsubscribed_at' => $data->unsubscribedAt,
        ]);

        return $recipient->refresh()->load(['subscriber', 'campaign']);
    }
}
