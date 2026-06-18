<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class NewsletterCampaignResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'subject' => $this->resource->subject,
            'preview_text' => $this->resource->preview_text,
            'content_markdown' => $this->resource->content_markdown,
            'content_html' => $this->resource->content_html,
            'status' => $this->resource->status?->value ?? $this->resource->status,
            'scheduled_at' => $this->resource->scheduled_at?->toISOString(),
            'sent_at' => $this->resource->sent_at?->toISOString(),
            'metadata' => $this->resource->metadata ?? [],
            'recipients_count' => $this->whenCounted('recipients'),
            'creator' => $this->whenLoaded('creator', fn (): ?array => $this->resource->creator === null ? null : [
                'id' => $this->resource->creator->id,
                'name' => $this->resource->creator->name,
                'email' => $this->resource->creator->email,
            ]),
            'recipients' => NewsletterCampaignRecipientResource::collection($this->whenLoaded('recipients')),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
