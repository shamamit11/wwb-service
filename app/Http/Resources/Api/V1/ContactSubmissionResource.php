<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class ContactSubmissionResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'topic' => $this->resource->topic,
            'message' => $this->resource->message,
            'status' => $this->resource->status,
            'admin_notes' => $this->resource->admin_notes,
            'metadata' => $this->resource->metadata ?? [],
            'submitted_at' => $this->resource->submitted_at?->toISOString(),
            'reviewed_at' => $this->resource->reviewed_at?->toISOString(),
            'reviewed_by' => $this->whenLoaded('reviewedBy', fn (): ?array => $this->resource->reviewedBy === null ? null : [
                'id' => $this->resource->reviewedBy->id,
                'name' => $this->resource->reviewedBy->name,
                'email' => $this->resource->reviewedBy->email,
            ]),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
