<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Ai\Data\QueuePostRewriteData;
use App\Modules\Posts\Services\RewritePostDraftService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QueuePostRewriteRequest extends FormRequest
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
            'scope' => ['required', 'string', Rule::in([
                RewritePostDraftService::SCOPE_FULL_DRAFT,
                RewritePostDraftService::SCOPE_SECTION,
                RewritePostDraftService::SCOPE_PARAGRAPH,
            ])],
            'target_block_ids' => ['sometimes', 'array'],
            'target_block_ids.*' => ['integer', 'exists:post_blocks,id'],
            'instructions' => ['sometimes', 'nullable', 'string'],
            'prompt_template_key' => ['sometimes', 'nullable', 'string', 'max:190'],
        ];
    }

    public function toData(): QueuePostRewriteData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return new QueuePostRewriteData(
            scope: (string) $validated['scope'],
            targetBlockIds: array_values(array_map('intval', $validated['target_block_ids'] ?? [])),
            instructions: isset($validated['instructions']) ? (string) $validated['instructions'] : null,
            promptTemplateKey: $validated['prompt_template_key'] ?? null,
        );
    }
}
