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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('content_topics', 'slug')->ignore($contentTopic->id)],
            'cluster' => ['required', 'string', Rule::in(ContentTopic::CLUSTERS)],
            'primary_keyword' => ['nullable', 'string', 'max:255'],
            'secondary_keywords' => ['nullable', 'array'],
            'secondary_keywords.*' => ['string', 'max:255'],
            'search_intent' => ['nullable', 'string', 'max:255'],
            'priority_score' => ['nullable', 'numeric', 'between:0,999.99'],
            'difficulty_note' => ['nullable', 'string'],
            'source' => ['required', 'string', Rule::in(ContentTopic::SOURCES)],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function toData(): UpdateContentTopicData
    {
        /** @var array{title:string,slug?:string|null,cluster:string,primary_keyword?:string|null,secondary_keywords?:array<int,string>|null,search_intent?:string|null,priority_score?:int|float|string|null,difficulty_note?:string|null,source:string,notes?:string|null} $validated */
        $validated = $this->validated();

        return new UpdateContentTopicData(
            title: $validated['title'],
            slug: $validated['slug'] ?? null,
            cluster: $validated['cluster'],
            primaryKeyword: $validated['primary_keyword'] ?? null,
            secondaryKeywords: array_values($validated['secondary_keywords'] ?? []),
            searchIntent: $validated['search_intent'] ?? null,
            priorityScore: isset($validated['priority_score']) ? (string) $validated['priority_score'] : null,
            difficultyNote: $validated['difficulty_note'] ?? null,
            source: $validated['source'],
            notes: $validated['notes'] ?? null,
        );
    }
}
