<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\ContentTopic;
use App\Modules\ContentTopics\Data\CreateContentTopicData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('content_topics', 'slug')],
            'cluster' => ['required', 'string', Rule::in(ContentTopic::CLUSTERS)],
            'primary_keyword' => ['nullable', 'string', 'max:255'],
            'secondary_keywords' => ['nullable', 'array'],
            'secondary_keywords.*' => ['string', 'max:255'],
            'search_intent' => ['nullable', 'string', 'max:'.ContentTopic::SEARCH_INTENT_MAX_LENGTH],
            'priority_score' => ['nullable', 'numeric', 'between:0,999.99'],
            'score_breakdown' => ['nullable', 'array'],
            'score_breakdown.trend_score' => ['nullable', 'numeric', 'between:0,35'],
            'score_breakdown.knowledge_base_fit' => ['nullable', 'numeric', 'between:0,20'],
            'score_breakdown.business_value' => ['nullable', 'numeric', 'between:0,20'],
            'score_breakdown.originality_gap' => ['nullable', 'numeric', 'between:0,15'],
            'score_breakdown.execution_confidence' => ['nullable', 'numeric', 'between:0,10'],
            'discovery_metadata' => ['nullable', 'array'],
            'difficulty_note' => ['nullable', 'string'],
            'source' => ['nullable', 'string', Rule::in(ContentTopic::SOURCES)],
            'status' => ['nullable', 'string', Rule::in(ContentTopic::STATUSES)],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function toData(): CreateContentTopicData
    {
        /** @var array{category_id:int,title:string,slug?:string|null,cluster:string,primary_keyword?:string|null,secondary_keywords?:array<int,string>|null,search_intent?:string|null,priority_score?:int|float|string|null,score_breakdown?:array<string,int|float|string|null>|null,discovery_metadata?:array<string,mixed>|null,difficulty_note?:string|null,source?:string|null,status?:string|null,notes?:string|null} $validated */
        $validated = $this->validated();

        return new CreateContentTopicData(
            categoryId: $validated['category_id'],
            title: $validated['title'],
            slug: $validated['slug'] ?? null,
            cluster: $validated['cluster'],
            primaryKeyword: $validated['primary_keyword'] ?? null,
            secondaryKeywords: array_values($validated['secondary_keywords'] ?? []),
            searchIntent: $validated['search_intent'] ?? null,
            priorityScore: isset($validated['priority_score']) ? (string) $validated['priority_score'] : null,
            scoreBreakdown: is_array($validated['score_breakdown'] ?? null) ? $validated['score_breakdown'] : null,
            discoveryMetadata: is_array($validated['discovery_metadata'] ?? null) ? $validated['discovery_metadata'] : null,
            difficultyNote: $validated['difficulty_note'] ?? null,
            source: $validated['source'] ?? ContentTopic::SOURCE_MANUAL,
            status: $validated['status'] ?? ContentTopic::STATUS_SUGGESTED,
            notes: $validated['notes'] ?? null,
        );
    }
}
