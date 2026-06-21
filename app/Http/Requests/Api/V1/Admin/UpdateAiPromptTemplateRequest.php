<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\AiPromptTemplate;
use App\Modules\Ai\Data\UpdateAiPromptTemplateData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAiPromptTemplateRequest extends FormRequest
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
        /** @var AiPromptTemplate $template */
        $template = $this->route('aiPrompt');

        return [
            'name' => ['required', 'string', 'max:160'],
            'key' => ['required', 'string', 'max:180', 'alpha_dash:ascii', Rule::in(AiPromptTemplate::MANAGED_KEYS), Rule::unique('ai_prompt_templates', 'key')->ignore($template->id)],
            'type' => ['required', 'string', Rule::in(AiPromptTemplate::MANAGED_TYPES)],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(AiPromptTemplate::STATUSES)],
        ];
    }

    public function toData(): UpdateAiPromptTemplateData
    {
        /** @var array{name:string,key:string,type:string,description?:string|null,status:string} $validated */
        $validated = $this->validated();
        $expectedType = AiPromptTemplate::MANAGED_KEY_TYPE_MAP[$validated['key']] ?? null;

        if ($expectedType !== $validated['type']) {
            abort(422, 'Prompt template key and type do not match the supported standard prompt families.');
        }

        return new UpdateAiPromptTemplateData(
            name: $validated['name'],
            key: $validated['key'],
            type: $validated['type'],
            description: $validated['description'] ?? null,
            status: $validated['status'],
        );
    }
}
