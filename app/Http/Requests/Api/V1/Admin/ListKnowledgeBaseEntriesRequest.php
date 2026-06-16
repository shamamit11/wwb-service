<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\KnowledgeBaseEntryFiltersData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListKnowledgeBaseEntriesRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::in(KnowledgeBaseEntry::STATUSES)],
            'entry_type' => ['nullable', 'string', Rule::in(KnowledgeBaseEntry::ENTRY_TYPES)],
            'created_by_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'featured_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'sort' => ['nullable', 'string', Rule::in(['title', '-title', 'created_at', '-created_at', 'updated_at', '-updated_at'])],
        ];
    }

    public function toData(): KnowledgeBaseEntryFiltersData
    {
        /** @var array{search?:string|null,status?:string|null,entry_type?:string|null,created_by_user_id?:int|null,featured_media_id?:int|null,sort?:string|null} $validated */
        $validated = $this->validated();

        return new KnowledgeBaseEntryFiltersData(
            search: $validated['search'] ?? null,
            status: $validated['status'] ?? null,
            entryType: $validated['entry_type'] ?? null,
            createdByUserId: $validated['created_by_user_id'] ?? null,
            featuredMediaId: $validated['featured_media_id'] ?? null,
            sort: $validated['sort'] ?? '-updated_at',
        );
    }
}
