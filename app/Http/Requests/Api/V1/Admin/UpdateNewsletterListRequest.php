<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\NewsletterList;
use App\Modules\Newsletter\Data\UpdateNewsletterListData;
use App\Modules\Newsletter\Enums\NewsletterListStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsletterListRequest extends FormRequest
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
        /** @var NewsletterList $newsletterList */
        $newsletterList = $this->route('newsletterList');

        return [
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('newsletter_lists', 'slug')->ignore($newsletterList->id)],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(array_map(static fn (NewsletterListStatus $status): string => $status->value, NewsletterListStatus::cases()))],
        ];
    }

    public function toData(): UpdateNewsletterListData
    {
        /** @var array{name:string,slug?:string|null,description?:string|null,status:string} $validated */
        $validated = $this->validated();

        return new UpdateNewsletterListData(
            name: $validated['name'],
            slug: $validated['slug'] ?? '',
            description: $validated['description'] ?? null,
            status: $validated['status'],
        );
    }
}
