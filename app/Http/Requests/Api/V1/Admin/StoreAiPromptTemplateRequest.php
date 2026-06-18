<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use App\Modules\Ai\Data\CreateAiPromptTemplateData;
use App\Modules\Ai\Data\CreateAiPromptTemplateVersionData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiPromptTemplateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:160'],
            'key' => ['required', 'string', 'max:180', 'alpha_dash:ascii', 'unique:ai_prompt_templates,key'],
            'type' => ['required', 'string', Rule::in(AiPromptTemplate::TYPES)],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(AiPromptTemplate::STATUSES)],
            'initial_version' => ['required', 'array'],
            'initial_version.system_prompt' => ['required', 'string'],
            'initial_version.user_prompt' => ['required', 'string'],
            'initial_version.output_schema' => ['nullable', 'array'],
            'initial_version.variables' => ['nullable', 'array'],
            'initial_version.variables.*' => ['string', 'max:120', 'distinct'],
            'initial_version.status' => ['sometimes', 'string', Rule::in(AiPromptTemplateVersion::STATUSES)],
        ];
    }

    public function toData(): CreateAiPromptTemplateData
    {
        /** @var array{name:string,key:string,type:string,description?:string|null,status:string,initial_version:array{system_prompt:string,user_prompt:string,output_schema?:array<string,mixed>|null,variables?:array<int,string>|null,status?:string|null}} $validated */
        $validated = $this->validated();
        $version = $validated['initial_version'];

        return new CreateAiPromptTemplateData(
            name: $validated['name'],
            key: $validated['key'],
            type: $validated['type'],
            description: $validated['description'] ?? null,
            status: $validated['status'],
            initialVersion: new CreateAiPromptTemplateVersionData(
                systemPrompt: $version['system_prompt'],
                userPrompt: $version['user_prompt'],
                outputSchema: $version['output_schema'] ?? null,
                variables: $version['variables'] ?? [],
                status: $version['status'] ?? AiPromptTemplateVersion::STATUS_ACTIVE,
            ),
        );
    }
}
