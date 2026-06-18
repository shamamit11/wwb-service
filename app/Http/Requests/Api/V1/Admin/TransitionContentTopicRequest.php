<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TransitionContentTopicRequest extends FormRequest
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
            'notes' => ['nullable', 'string'],
        ];
    }

    public function notes(): ?string
    {
        /** @var array{notes?:string|null} $validated */
        $validated = $this->validated();

        return $validated['notes'] ?? null;
    }
}
