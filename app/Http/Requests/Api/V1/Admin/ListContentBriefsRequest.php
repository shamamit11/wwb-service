<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\ContentBrief;
use App\Modules\ContentBriefs\Data\ContentBriefFiltersData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListContentBriefsRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::in(ContentBrief::STATUSES)],
            'content_topic_id' => ['nullable', 'integer', 'exists:content_topics,id'],
            'sort' => ['nullable', 'string', Rule::in([
                'created_at',
                '-created_at',
                'updated_at',
                '-updated_at',
                'approved_at',
                '-approved_at',
                'title',
                '-title',
            ])],
        ];
    }

    public function toData(): ContentBriefFiltersData
    {
        /** @var array{search?:string|null,status?:string|null,content_topic_id?:int|null,sort?:string|null} $validated */
        $validated = $this->validated();

        return new ContentBriefFiltersData(
            search: $validated['search'] ?? null,
            status: $validated['status'] ?? null,
            contentTopicId: $validated['content_topic_id'] ?? null,
            sort: $validated['sort'] ?? '-created_at',
        );
    }
}
