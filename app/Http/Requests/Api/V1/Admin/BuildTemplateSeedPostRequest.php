<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Templates\Data\TemplatePayloadContextData;
use Illuminate\Foundation\Http\FormRequest;

class BuildTemplateSeedPostRequest extends FormRequest
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
            'title' => ['nullable', 'string', 'max:160'],
            'topic' => ['nullable', 'string', 'max:160'],
        ];
    }

    public function toData(): TemplatePayloadContextData
    {
        /** @var array{title?:string|null,topic?:string|null} $validated */
        $validated = $this->validated();

        return new TemplatePayloadContextData(
            title: $validated['title'] ?? null,
            topic: $validated['topic'] ?? null,
        );
    }
}
