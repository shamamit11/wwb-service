<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\UpdateKnowledgeBaseEntryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKnowledgeBaseEntryRequest extends FormRequest
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
        /** @var KnowledgeBaseEntry $entry */
        $entry = $this->route('knowledgeBase');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('knowledge_base_entries', 'slug')->ignore($entry->id)],
            'entry_type' => ['required', 'string', Rule::in(KnowledgeBaseEntry::ENTRY_TYPES)],
            'status' => ['required', 'string', Rule::in(KnowledgeBaseEntry::STATUSES)],
            'summary' => ['nullable', 'string'],
            'content_markdown' => ['required', 'string'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'featured_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function toData(int $userId): UpdateKnowledgeBaseEntryData
    {
        /** @var array{title:string,slug?:string|null,entry_type:string,status:string,summary?:string|null,content_markdown:string,source_url?:string|null,featured_media_id?:int|null,metadata?:array<string,mixed>|null} $validated */
        $validated = $this->validated();

        return new UpdateKnowledgeBaseEntryData(
            updatedByUserId: $userId,
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
