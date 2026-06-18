<?php

namespace App\Http\Requests\Api\V1\Public;

use App\Modules\Newsletter\Data\PublicSubscribeNewsletterData;
use Illuminate\Foundation\Http\FormRequest;

class SubscribeNewsletterRequest extends FormRequest
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
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'name' => ['nullable', 'string', 'max:160'],
            'source' => ['nullable', 'string', 'max:160'],
            'list_ids' => ['nullable', 'array'],
            'list_ids.*' => ['integer', 'distinct', 'exists:newsletter_lists,id'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function toData(): PublicSubscribeNewsletterData
    {
        /** @var array{email:string,name?:string|null,source?:string|null,list_ids?:array<int,int>|null,metadata?:array<string,mixed>|null} $validated */
        $validated = $this->validated();

        return new PublicSubscribeNewsletterData(
            email: $validated['email'],
            name: $validated['name'] ?? null,
            source: $validated['source'] ?? 'public',
            listIds: array_values($validated['list_ids'] ?? []),
            metadata: $validated['metadata'] ?? null,
        );
    }
}
