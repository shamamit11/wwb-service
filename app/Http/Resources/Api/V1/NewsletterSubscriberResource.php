<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class NewsletterSubscriberResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'email' => $this->resource->email,
            'name' => $this->resource->name,
            'status' => $this->resource->status?->value ?? $this->resource->status,
            'source' => $this->resource->source,
            'subscribed_at' => $this->resource->subscribed_at?->toISOString(),
            'unsubscribed_at' => $this->resource->unsubscribed_at?->toISOString(),
            'unsubscribe_token' => $this->resource->unsubscribe_token,
            'metadata' => $this->resource->metadata ?? [],
            'lists' => NewsletterListResource::collection($this->whenLoaded('lists')),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
