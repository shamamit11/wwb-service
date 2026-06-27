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
            'queue_draft' => ['sometimes', 'boolean'],
        ];
    }

    public function notes(): ?string
    {
        /** @var array{notes?:string|null,queue_draft?:bool|null} $validated */
        $validated = $this->validated();

        return $validated['notes'] ?? null;
    }

    public function queueDraft(): bool
    {
        /** @var array{notes?:string|null,queue_draft?:bool|null} $validated */
        $validated = $this->validated();

        return (bool) ($validated['queue_draft'] ?? false);
    }
}
