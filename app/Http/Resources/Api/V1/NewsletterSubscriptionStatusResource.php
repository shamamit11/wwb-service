<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class NewsletterSubscriptionStatusResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'email' => $this->resource->email,
            'status' => $this->resource->status?->value ?? $this->resource->status,
            'subscribed_at' => $this->resource->subscribed_at?->toISOString(),
            'unsubscribed_at' => $this->resource->unsubscribed_at?->toISOString(),
            'lists' => NewsletterListResource::collection($this->resource->relationLoaded('lists') ? $this->resource->lists : collect()),
        ];
    }
}
