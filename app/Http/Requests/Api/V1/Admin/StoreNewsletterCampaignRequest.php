<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Newsletter\Data\CreateNewsletterCampaignData;
use App\Modules\Newsletter\Enums\NewsletterCampaignStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsletterCampaignRequest extends FormRequest
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
            'subject' => ['required', 'string', 'max:255'],
            'preview_text' => ['nullable', 'string', 'max:255'],
            'content_markdown' => ['nullable', 'string'],
            'content_html' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', Rule::in(array_map(static fn (NewsletterCampaignStatus $status): string => $status->value, NewsletterCampaignStatus::cases()))],
            'scheduled_at' => ['nullable', 'date'],
            'sent_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function toData(int $userId): CreateNewsletterCampaignData
    {
        /** @var array{title:string,subject:string,preview_text?:string|null,content_markdown?:string|null,content_html?:string|null,status?:string,scheduled_at?:string|null,sent_at?:string|null,metadata?:array<string,mixed>|null} $validated */
        $validated = $this->validated();

        return new CreateNewsletterCampaignData(
            title: $validated['title'],
            subject: $validated['subject'],
            previewText: $validated['preview_text'] ?? null,
            contentMarkdown: $validated['content_markdown'] ?? null,
            contentHtml: $validated['content_html'] ?? null,
            status: $validated['status'] ?? NewsletterCampaignStatus::Draft->value,
            scheduledAt: $validated['scheduled_at'] ?? null,
            sentAt: $validated['sent_at'] ?? null,
            createdBy: $userId,
            metadata: $validated['metadata'] ?? null,
        );
    }
}
