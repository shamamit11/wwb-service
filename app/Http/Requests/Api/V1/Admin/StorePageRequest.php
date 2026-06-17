<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Page;
use App\Modules\Pages\Data\CreatePageData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
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
            'slug' => ['nullable', 'string', 'max:190', 'unique:pages,slug'],
            'type' => ['required', 'string', Rule::in(Page::TYPES)],
            'status' => ['required', 'string', Rule::in(Page::STATUSES)],
            'summary' => ['nullable', 'string'],
            'content_markdown' => ['required', 'string'],
            'visibility' => ['required', 'string', Rule::in(Page::VISIBILITIES)],
            'published_at' => ['nullable', 'date'],
            'scheduled_for' => ['nullable', 'date'],
            'meta' => ['nullable', 'array'],
        ];
    }

    public function toData(int $userId): CreatePageData
    {
        /** @var array{title:string,slug?:string|null,type:string,status:string,summary?:string|null,content_markdown:string,visibility:string,published_at?:string|null,scheduled_for?:string|null,meta?:array<string,mixed>|null} $validated */
        $validated = $this->validated();

        return new CreatePageData(
            createdByUserId: $userId,
            updatedByUserId: null,
            title: $validated['title'],
            slug: $validated['slug'] ?? '',
            type: $validated['type'],
            status: $validated['status'],
            summary: $validated['summary'] ?? null,
            contentMarkdown: $validated['content_markdown'],
            visibility: $validated['visibility'],
            publishedAt: $validated['published_at'] ?? null,
            scheduledFor: $validated['scheduled_for'] ?? null,
            meta: $validated['meta'] ?? null,
        );
    }
}
