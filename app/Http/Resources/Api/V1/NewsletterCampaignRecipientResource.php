<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class NewsletterCampaignRecipientResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'newsletter_campaign_id' => $this->resource->newsletter_campaign_id,
            'newsletter_subscriber_id' => $this->resource->newsletter_subscriber_id,
            'email' => $this->resource->email,
            'status' => $this->resource->status?->value ?? $this->resource->status,
            'error_message' => $this->resource->error_message,
            'sent_at' => $this->resource->sent_at?->toISOString(),
            'failed_at' => $this->resource->failed_at?->toISOString(),
            'opened_at' => $this->resource->opened_at?->toISOString(),
            'clicked_at' => $this->resource->clicked_at?->toISOString(),
            'unsubscribed_at' => $this->resource->unsubscribed_at?->toISOString(),
            'subscriber' => $this->whenLoaded('subscriber', fn () => new NewsletterSubscriberResource($this->resource->subscriber)),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
