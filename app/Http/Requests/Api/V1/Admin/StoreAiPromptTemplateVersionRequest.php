<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\AiPromptTemplateVersion;
use App\Modules\Ai\Data\CreateAiPromptTemplateVersionData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiPromptTemplateVersionRequest extends FormRequest
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
            'system_prompt' => ['required', 'string'],
            'user_prompt' => ['required', 'string'],
            'output_schema' => ['nullable', 'array'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['string', 'max:120', 'distinct'],
            'status' => ['sometimes', 'string', Rule::in(AiPromptTemplateVersion::STATUSES)],
        ];
    }

    public function toData(): CreateAiPromptTemplateVersionData
    {
        /** @var array{system_prompt:string,user_prompt:string,output_schema?:array<string,mixed>|null,variables?:array<int,string>|null,status?:string|null} $validated */
        $validated = $this->validated();

        return new CreateAiPromptTemplateVersionData(
            systemPrompt: $validated['system_prompt'],
            userPrompt: $validated['user_prompt'],
            outputSchema: $validated['output_schema'] ?? null,
            variables: $validated['variables'] ?? [],
            status: $validated['status'] ?? AiPromptTemplateVersion::STATUS_DRAFT,
        );
    }
}
