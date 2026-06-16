<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\Admin\Concerns\InteractsWithTemplateData;
use App\Models\Template;
use App\Models\TemplateBlock;
use App\Modules\Templates\Data\UpdateTemplateData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTemplateRequest extends FormRequest
{
    use InteractsWithTemplateData;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Template $template */
        $template = $this->route('template');

        return [
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('templates', 'slug')->ignore($template->id)],
            'template_type' => ['required', 'string', Rule::in(Template::TEMPLATE_TYPES)],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(Template::STATUSES)],
            'default_excerpt_prompt' => ['nullable', 'string'],
            'default_meta' => ['nullable', 'array'],
            'blocks' => ['required', 'array', 'min:1'],
            'blocks.*.block_type' => ['required', 'string', Rule::in(TemplateBlock::BLOCK_TYPES)],
            'blocks.*.sort_order' => ['required', 'integer', 'min:1', 'distinct'],
            'blocks.*.label' => ['nullable', 'string', 'max:160'],
            'blocks.*.default_markdown' => ['nullable', 'string'],
            'blocks.*.settings' => ['nullable', 'array'],
            'blocks.*.is_required' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(int $userId): UpdateTemplateData
    {
        /** @var array{name:string,slug?:string|null,template_type:string,description?:string|null,status:string,default_excerpt_prompt?:string|null,default_meta?:array<string,mixed>|null,blocks:array<int, array{block_type:string,sort_order:int,label?:string|null,default_markdown?:string|null,settings?:array<string,mixed>|null,is_required?:bool}>} $validated */
        $validated = $this->validated();

        return new UpdateTemplateData(
            updatedByUserId: $userId,
            name: $validated['name'],
            slug: $validated['slug'] ?? '',
            templateType: $validated['template_type'],
            description: $validated['description'] ?? null,
            status: $validated['status'],
            defaultExcerptPrompt: $validated['default_excerpt_prompt'] ?? null,
            defaultMeta: $validated['default_meta'] ?? null,
            blocks: $this->mapBlocks($validated['blocks']),
        );
    }
}
