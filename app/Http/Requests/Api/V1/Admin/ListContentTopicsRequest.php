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
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'cluster' => ['nullable', 'string', Rule::in(ContentTopic::CLUSTERS)],
            'source' => ['nullable', 'string', Rule::in(ContentTopic::SOURCES)],
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
        /** @var array{search?:string|null,status?:string|null,category_id?:int|null,cluster?:string|null,source?:string|null,sort?:string|null} $validated */
        $validated = $this->validated();

        return new ContentTopicFiltersData(
            search: $validated['search'] ?? null,
            status: $validated['status'] ?? null,
            categoryId: $validated['category_id'] ?? null,
            cluster: $validated['cluster'] ?? null,
            source: $validated['source'] ?? null,
            sort: $validated['sort'] ?? '-created_at',
        );
    }
}
