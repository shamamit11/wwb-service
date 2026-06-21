<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class NewsItemResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'external_id' => $this->resource->external_id,
            'provider' => $this->resource->provider,
            'status' => $this->resource->status,
            'title' => $this->resource->title,
            'normalized_title' => $this->resource->normalized_title,
            'url' => $this->resource->url,
            'canonical_url' => $this->resource->canonical_url,
            'description' => $this->resource->description,
            'author' => $this->resource->author,
            'publisher_name' => $this->resource->publisher_name,
            'language' => $this->resource->language,
            'country' => $this->resource->country,
            'metadata' => $this->resource->metadata ?? [],
            'published_at' => $this->resource->published_at?->toISOString(),
            'discovered_at' => $this->resource->discovered_at?->toISOString(),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'category' => $this->whenLoaded('category', fn (): ?array => $this->resource->category === null ? null : [
                'id' => $this->resource->category->id,
                'name' => $this->resource->category->name,
                'slug' => $this->resource->category->slug,
            ]),
            'source' => $this->whenLoaded('source', fn (): ?array => $this->resource->source === null ? null : [
                'id' => $this->resource->source->id,
                'name' => $this->resource->source->name,
                'slug' => $this->resource->source->slug,
                'kind' => $this->resource->source->kind,
                'base_url' => $this->resource->source->base_url,
                'trust_score' => $this->resource->source->trust_score,
            ]),
            'latest_score' => $this->whenLoaded('latestScore', fn (): ?array => $this->resource->latestScore === null ? null : [
                'id' => $this->resource->latestScore->id,
                'relevance_score' => $this->resource->latestScore->relevance_score,
                'freshness_score' => $this->resource->latestScore->freshness_score,
                'credibility_score' => $this->resource->latestScore->credibility_score,
                'pillar_fit_score' => $this->resource->latestScore->pillar_fit_score,
                'evergreen_potential_score' => $this->resource->latestScore->evergreen_potential_score,
                'novelty_score' => $this->resource->latestScore->novelty_score,
                'business_value_score' => $this->resource->latestScore->business_value_score,
                'total_score' => $this->resource->latestScore->total_score,
                'decision' => $this->resource->latestScore->decision,
                'reasoning' => $this->resource->latestScore->reasoning,
                'scored_at' => $this->resource->latestScore->scored_at?->toISOString(),
            ]),
            'latest_extraction' => $this->whenLoaded('latestExtraction', fn (): ?array => $this->resource->latestExtraction === null ? null : [
                'id' => $this->resource->latestExtraction->id,
                'extractor' => $this->resource->latestExtraction->extractor,
                'excerpt' => $this->resource->latestExtraction->excerpt,
                'facts_json' => $this->resource->latestExtraction->facts_json ?? [],
                'entities_json' => $this->resource->latestExtraction->entities_json ?? [],
                'claims_json' => $this->resource->latestExtraction->claims_json ?? [],
                'metadata' => $this->resource->latestExtraction->metadata ?? [],
                'extracted_at' => $this->resource->latestExtraction->extracted_at?->toISOString(),
            ]),
            'latest_route' => $this->whenLoaded('latestRoute', fn (): ?array => $this->resource->latestRoute === null ? null : [
                'id' => $this->resource->latestRoute->id,
                'route' => $this->resource->latestRoute->route,
                'knowledge_base_entry_id' => $this->resource->latestRoute->knowledge_base_entry_id,
                'content_topic_id' => $this->resource->latestRoute->content_topic_id,
                'post_id' => $this->resource->latestRoute->post_id,
                'metadata' => $this->resource->latestRoute->metadata ?? [],
                'routed_at' => $this->resource->latestRoute->routed_at?->toISOString(),
                'knowledge_base_entry' => $this->resource->latestRoute->relationLoaded('knowledgeBaseEntry') && $this->resource->latestRoute->knowledgeBaseEntry !== null ? [
                    'id' => $this->resource->latestRoute->knowledgeBaseEntry->id,
                    'title' => $this->resource->latestRoute->knowledgeBaseEntry->title,
                    'slug' => $this->resource->latestRoute->knowledgeBaseEntry->slug,
                ] : null,
                'content_topic' => $this->resource->latestRoute->relationLoaded('contentTopic') && $this->resource->latestRoute->contentTopic !== null ? [
                    'id' => $this->resource->latestRoute->contentTopic->id,
                    'title' => $this->resource->latestRoute->contentTopic->title,
                    'slug' => $this->resource->latestRoute->contentTopic->slug,
                    'status' => $this->resource->latestRoute->contentTopic->status,
                ] : null,
                'post' => $this->resource->latestRoute->relationLoaded('post') && $this->resource->latestRoute->post !== null ? [
                    'id' => $this->resource->latestRoute->post->id,
                    'title' => $this->resource->latestRoute->post->title,
                    'slug' => $this->resource->latestRoute->post->slug,
                    'status' => $this->resource->latestRoute->post->status,
                ] : null,
            ]),
        ];
    }
}
