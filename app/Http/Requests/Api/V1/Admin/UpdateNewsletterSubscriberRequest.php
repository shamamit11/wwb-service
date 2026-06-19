<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Data\UpdateNewsletterSubscriberData;
use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsletterSubscriberRequest extends FormRequest
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
        /** @var NewsletterSubscriber $newsletterSubscriber */
        $newsletterSubscriber = $this->route('newsletterSubscriber');

        return [
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('newsletter_subscribers', 'email')->ignore($newsletterSubscriber->id)],
            'name' => ['nullable', 'string', 'max:160'],
            'status' => ['required', 'string', Rule::in(array_map(static fn (NewsletterSubscriberStatus $status): string => $status->value, NewsletterSubscriberStatus::cases()))],
            'source' => ['nullable', 'string', 'max:160'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function toData(): UpdateNewsletterSubscriberData
    {
        /** @var array{email:string,name?:string|null,status:string,source?:string|null,metadata?:array<string,mixed>|null} $validated */
        $validated = $this->validated();

        return new UpdateNewsletterSubscriberData(
            email: $validated['email'],
            name: $validated['name'] ?? null,
            status: $validated['status'],
            source: $validated['source'] ?? null,
            subscribedAt: null,
            unsubscribedAt: null,
            metadata: $validated['metadata'] ?? null,
        );
    }
}
