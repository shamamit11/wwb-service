<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class ContentBriefResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'content_topic_id' => $this->resource->content_topic_id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'meta_title' => $this->resource->meta_title,
            'meta_description' => $this->resource->meta_description,
            'primary_keyword' => $this->resource->primary_keyword,
            'secondary_keywords' => $this->resource->secondary_keywords ?? [],
            'search_intent' => $this->resource->search_intent,
            'outline' => $this->resource->outline ?? [],
            'headings' => $this->resource->headings ?? [],
            'faq_suggestions' => $this->resource->faq_suggestions ?? [],
            'internal_link_suggestions' => $this->resource->internal_link_suggestions ?? [],
            'image_suggestions' => $this->resource->image_suggestions ?? [],
            'status' => $this->resource->status,
            'can_generate_draft' => $this->resource->canGenerateDraft(),
            'approved_at' => $this->resource->approved_at?->toISOString(),
            'topic' => $this->whenLoaded('topic', fn (): array => [
                'id' => $this->resource->topic->id,
                'title' => $this->resource->topic->title,
                'slug' => $this->resource->topic->slug,
                'cluster' => $this->resource->topic->cluster,
                'status' => $this->resource->topic->status,
            ]),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
