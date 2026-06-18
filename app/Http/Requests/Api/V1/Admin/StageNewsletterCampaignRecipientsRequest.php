<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Newsletter\Data\StageNewsletterCampaignRecipientsData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StageNewsletterCampaignRecipientsRequest extends FormRequest
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
            'subscriber_ids' => ['sometimes', 'array'],
            'subscriber_ids.*' => ['integer', 'distinct', 'exists:newsletter_subscribers,id'],
            'list_ids' => ['sometimes', 'array'],
            'list_ids.*' => ['integer', 'distinct', 'exists:newsletter_lists,id'],
            'include_all_active_subscribers' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $payload = $this->validated();
            $hasSubscribers = ($payload['subscriber_ids'] ?? []) !== [];
            $hasLists = ($payload['list_ids'] ?? []) !== [];
            $includeAll = (bool) ($payload['include_all_active_subscribers'] ?? false);

            if (! $hasSubscribers && ! $hasLists && ! $includeAll) {
                $validator->errors()->add(
                    'subscriber_ids',
                    'Provide subscriber_ids, list_ids, or include_all_active_subscribers.',
                );
            }
        });
    }

    public function toData(): StageNewsletterCampaignRecipientsData
    {
        /** @var array{subscriber_ids?:array<int,int>,list_ids?:array<int,int>,include_all_active_subscribers?:bool} $validated */
        $validated = $this->validated();

        return new StageNewsletterCampaignRecipientsData(
            subscriberIds: array_values($validated['subscriber_ids'] ?? []),
            listIds: array_values($validated['list_ids'] ?? []),
            includeAllActiveSubscribers: (bool) ($validated['include_all_active_subscribers'] ?? false),
        );
    }
}
