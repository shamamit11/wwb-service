<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Page;
use App\Modules\Pages\Data\PageFiltersData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPagesRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::in(Page::STATUSES)],
            'type' => ['nullable', 'string', Rule::in(Page::TYPES)],
            'visibility' => ['nullable', 'string', Rule::in(Page::VISIBILITIES)],
            'created_by_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'sort' => ['nullable', 'string', Rule::in(['title', '-title', 'created_at', '-created_at', 'updated_at', '-updated_at', 'published_at', '-published_at'])],
        ];
    }

    public function toData(): PageFiltersData
    {
        /** @var array{search?:string|null,status?:string|null,type?:string|null,visibility?:string|null,created_by_user_id?:int|null,sort?:string|null} $validated */
        $validated = $this->validated();

        return new PageFiltersData(
            search: $validated['search'] ?? null,
            status: $validated['status'] ?? null,
            type: $validated['type'] ?? null,
            visibility: $validated['visibility'] ?? null,
            createdByUserId: $validated['created_by_user_id'] ?? null,
            sort: $validated['sort'] ?? '-updated_at',
        );
    }
}
