<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsletterSubscriberRequest extends FormRequest
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
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:newsletter_subscribers,email'],
            'name' => ['nullable', 'string', 'max:160'],
            'source' => ['nullable', 'string', 'max:160'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array{email:string,name?:string|null,source?:string|null,metadata?:array<string,mixed>|null}
     */
    public function toPayload(): array
    {
        /** @var array{email:string,name?:string|null,source?:string|null,metadata?:array<string,mixed>|null} $validated */
        $validated = $this->validated();

        return $validated;
    }
}
