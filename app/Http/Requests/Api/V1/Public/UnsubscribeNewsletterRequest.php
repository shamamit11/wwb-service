<?php

namespace App\Http\Requests\Api\V1\Public;

use App\Modules\Newsletter\Data\PublicUnsubscribeNewsletterData;
use Illuminate\Foundation\Http\FormRequest;

class UnsubscribeNewsletterRequest extends FormRequest
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
            'token' => ['required', 'string', 'max:255'],
        ];
    }

    public function toData(): PublicUnsubscribeNewsletterData
    {
        /** @var array{token:string} $validated */
        $validated = $this->validated();

        return new PublicUnsubscribeNewsletterData(
            unsubscribeToken: $validated['token'],
        );
    }
}
