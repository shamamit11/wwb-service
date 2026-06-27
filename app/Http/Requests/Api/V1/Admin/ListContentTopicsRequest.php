<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\ContentTopic;
use App\Modules\ContentTopics\Data\ContentTopicFiltersData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListContentTopicsRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(ContentTopic::STATUSES)],
            'recommendation' => ['nullable', 'string', Rule::in(ContentTopic::RECOMMENDATIONS)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'cluster' => ['nullable', 'string', Rule::in(ContentTopic::CLUSTERS)],
            'source' => ['nullable', 'string', Rule::in(ContentTopic::SOURCES)],
            'is_duplicate' => ['nullable', 'boolean'],
            'has_draft_generation_job' => ['nullable', 'boolean'],
            'priority_score_min' => ['nullable', 'numeric', 'between:0,999.99'],
            'priority_score_max' => ['nullable', 'numeric', 'between:0,999.99'],
            'sort' => ['nullable', 'string', Rule::in([
                'created_at',
                '-created_at',
                'updated_at',
                '-updated_at',
                'approved_at',
                '-approved_at',
                'used_at',
                '-used_at',
                'priority_score',
                '-priority_score',
                'title',
                '-title',
            ])],
        ];
    }

    public function toData(): ContentTopicFiltersData
    {
        /** @var array{search?:string|null,status?:string|null,recommendation?:string|null,category_id?:int|null,cluster?:string|null,source?:string|null,is_duplicate?:bool|null,has_draft_generation_job?:bool|null,priority_score_min?:int|float|string|null,priority_score_max?:int|float|string|null,sort?:string|null} $validated */
        $validated = $this->validated();

        return new ContentTopicFiltersData(
            search: $validated['search'] ?? null,
            status: $validated['status'] ?? null,
            recommendation: $validated['recommendation'] ?? null,
            categoryId: $validated['category_id'] ?? null,
            cluster: $validated['cluster'] ?? null,
            source: $validated['source'] ?? null,
            isDuplicate: array_key_exists('is_duplicate', $validated) ? (bool) $validated['is_duplicate'] : null,
            hasDraftGenerationJob: array_key_exists('has_draft_generation_job', $validated) ? (bool) $validated['has_draft_generation_job'] : null,
            priorityScoreMin: isset($validated['priority_score_min']) ? (float) $validated['priority_score_min'] : null,
            priorityScoreMax: isset($validated['priority_score_max']) ? (float) $validated['priority_score_max'] : null,
            sort: $validated['sort'] ?? '-created_at',
        );
    }
}
