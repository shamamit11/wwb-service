<?php

namespace App\Modules\Newsletter\Repositories;

use App\Models\NewsletterCampaign;
use App\Modules\Newsletter\Data\CreateNewsletterCampaignData;
use App\Modules\Newsletter\Data\UpdateNewsletterCampaignData;
use Illuminate\Database\Eloquent\Collection;

class EloquentNewsletterCampaignRepository implements NewsletterCampaignRepository
{
    public function create(CreateNewsletterCampaignData $data): NewsletterCampaign
    {
        return NewsletterCampaign::query()->create([
            'title' => $data->title,
            'subject' => $data->subject,
            'preview_text' => $data->previewText,
            'content_markdown' => $data->contentMarkdown,
            'content_html' => $data->contentHtml,
            'status' => $data->status,
            'scheduled_at' => $data->scheduledAt,
            'sent_at' => $data->sentAt,
            'created_by' => $data->createdBy,
            'metadata' => $data->metadata,
        ])->refresh()->load(['creator', 'recipients']);
    }

    public function update(NewsletterCampaign $campaign, UpdateNewsletterCampaignData $data): NewsletterCampaign
    {
        $campaign->update([
            'title' => $data->title,
            'subject' => $data->subject,
            'preview_text' => $data->previewText,
            'content_markdown' => $data->contentMarkdown,
            'content_html' => $data->contentHtml,
            'status' => $data->status,
            'scheduled_at' => $data->scheduledAt,
            'sent_at' => $data->sentAt,
            'metadata' => $data->metadata,
        ]);

        return $this->findById((int) $campaign->id) ?? $campaign->refresh();
    }

    public function delete(NewsletterCampaign $campaign): void
    {
        $campaign->delete();
    }

    public function findById(int $id): ?NewsletterCampaign
    {
        return NewsletterCampaign::query()
            ->with(['creator', 'recipients.subscriber'])
            ->withCount('recipients')
            ->find($id);
    }

    /**
     * @return Collection<int, NewsletterCampaign>
     */
    public function getAllOrdered(): Collection
    {
        return NewsletterCampaign::query()
            ->with(['creator'])
            ->withCount('recipients')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }
}
