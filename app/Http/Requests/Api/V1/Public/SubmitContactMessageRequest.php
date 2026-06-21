<?php

namespace App\Http\Requests\Api\V1\Public;

use Illuminate\Foundation\Http\FormRequest;

class SubmitContactMessageRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'topic' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array{name:string,email:string,topic:string,message:string,metadata?:array<string,mixed>|null}
     */
    public function toPayload(): array
    {
        /** @var array{name:string,email:string,topic:string,message:string,metadata?:array<string,mixed>|null} $validated */
        $validated = $this->validated();

        return $validated;
    }
}
