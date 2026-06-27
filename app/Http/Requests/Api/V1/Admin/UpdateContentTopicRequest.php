<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\ContentTopic;
use App\Modules\ContentTopics\Data\UpdateContentTopicData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentTopicRequest extends FormRequest
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
        /** @var ContentTopic $contentTopic */
        $contentTopic = $this->route('contentTopic');

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('content_topics', 'slug')->ignore($contentTopic->id)],
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
            'source' => ['required', 'string', Rule::in(ContentTopic::SOURCES)],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function toData(): UpdateContentTopicData
    {
        /** @var array{category_id:int,title:string,slug?:string|null,cluster:string,primary_keyword?:string|null,secondary_keywords?:array<int,string>|null,search_intent?:string|null,priority_score?:int|float|string|null,score_breakdown?:array<string,int|float|string|null>|null,discovery_metadata?:array<string,mixed>|null,difficulty_note?:string|null,source:string,notes?:string|null} $validated */
        $validated = $this->validated();

        return new UpdateContentTopicData(
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
            source: $validated['source'],
            notes: $validated['notes'] ?? null,
        );
    }
}
