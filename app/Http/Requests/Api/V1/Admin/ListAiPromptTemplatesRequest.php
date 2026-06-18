<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\AiPromptTemplate;
use App\Modules\Ai\Data\AiPromptTemplateFiltersData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAiPromptTemplatesRequest extends FormRequest
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
            'type' => ['nullable', 'string', Rule::in(AiPromptTemplate::TYPES)],
            'status' => ['nullable', 'string', Rule::in(AiPromptTemplate::STATUSES)],
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', Rule::in(['name', '-name', 'key', '-key', 'type', '-type', 'created_at', '-created_at', 'updated_at', '-updated_at'])],
        ];
    }

    public function toData(): AiPromptTemplateFiltersData
    {
        /** @var array{type?:string|null,status?:string|null,search?:string|null,sort?:string|null} $validated */
        $validated = $this->validated();

        return new AiPromptTemplateFiltersData(
            type: $validated['type'] ?? null,
            status: $validated['status'] ?? null,
            search: $validated['search'] ?? null,
            sort: $validated['sort'] ?? 'name',
        );
    }
}
