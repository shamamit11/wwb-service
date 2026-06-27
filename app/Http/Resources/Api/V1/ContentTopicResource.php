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
            'category_id' => $this->resource->category_id,
            'category' => $this->whenLoaded('category', fn (): ?array => $this->resource->category === null ? null : [
                'id' => $this->resource->category->id,
                'name' => $this->resource->category->name,
                'slug' => $this->resource->category->slug,
            ]),
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'cluster' => $this->resource->cluster,
            'primary_keyword' => $this->resource->primary_keyword,
            'secondary_keywords' => $this->resource->secondary_keywords ?? [],
            'search_intent' => $this->resource->search_intent,
            'priority_score' => $this->resource->priority_score,
            'score_breakdown' => $this->resource->score_breakdown ?? null,
            'discovery_metadata' => $this->resource->discovery_metadata ?? null,
            'difficulty_note' => $this->resource->difficulty_note,
            'source' => $this->resource->source,
            'status' => $this->resource->status,
            'editorial_recommendation' => $this->resource->editorialRecommendation(),
            'is_duplicate' => $this->resource->isDuplicateDiscovery(),
            'duplicate_matches' => $this->resource->duplicateMatches(),
            'has_draft_generation_job' => $this->resource->hasDraftGenerationJob(),
            'notes' => $this->resource->notes,
            'can_generate_draft' => $this->resource->canGenerateDraft(),
            'approved_at' => $this->resource->approved_at?->toISOString(),
            'rejected_at' => $this->resource->rejected_at?->toISOString(),
            'used_at' => $this->resource->used_at?->toISOString(),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
