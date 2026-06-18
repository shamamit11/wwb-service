<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class ContentTopicResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'cluster' => $this->resource->cluster,
            'primary_keyword' => $this->resource->primary_keyword,
            'secondary_keywords' => $this->resource->secondary_keywords ?? [],
            'search_intent' => $this->resource->search_intent,
            'priority_score' => $this->resource->priority_score,
            'difficulty_note' => $this->resource->difficulty_note,
            'source' => $this->resource->source,
            'status' => $this->resource->status,
            'notes' => $this->resource->notes,
            'can_generate_content_brief' => $this->resource->canGenerateContentBrief(),
            'approved_at' => $this->resource->approved_at?->toISOString(),
            'rejected_at' => $this->resource->rejected_at?->toISOString(),
            'used_at' => $this->resource->used_at?->toISOString(),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
