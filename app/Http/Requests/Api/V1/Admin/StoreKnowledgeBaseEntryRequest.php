<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\CreateKnowledgeBaseEntryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKnowledgeBaseEntryRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:190', 'unique:knowledge_base_entries,slug'],
            'entry_type' => ['required', 'string', Rule::in(KnowledgeBaseEntry::ENTRY_TYPES)],
            'status' => ['required', 'string', Rule::in(KnowledgeBaseEntry::STATUSES)],
            'summary' => ['nullable', 'string'],
            'content_markdown' => ['required', 'string'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'featured_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function toData(int $userId): CreateKnowledgeBaseEntryData
    {
        /** @var array{title:string,slug?:string|null,entry_type:string,status:string,summary?:string|null,content_markdown:string,source_url?:string|null,featured_media_id?:int|null,metadata?:array<string,mixed>|null} $validated */
        $validated = $this->validated();

        return new CreateKnowledgeBaseEntryData(
            createdByUserId: $userId,
            updatedByUserId: null,
            title: $validated['title'],
            slug: $validated['slug'] ?? '',
            entryType: $validated['entry_type'],
            status: $validated['status'],
            summary: $validated['summary'] ?? null,
            contentMarkdown: $validated['content_markdown'],
            sourceUrl: $validated['source_url'] ?? null,
            featuredMediaId: $validated['featured_media_id'] ?? null,
            metadata: $validated['metadata'] ?? null,
        );
    }
}
