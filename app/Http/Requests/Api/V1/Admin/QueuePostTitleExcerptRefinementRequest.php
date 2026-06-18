<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Ai\Data\QueuePostTitleExcerptRefinementData;
use Illuminate\Foundation\Http\FormRequest;

class QueuePostTitleExcerptRefinementRequest extends FormRequest
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
            'instructions' => ['sometimes', 'nullable', 'string'],
            'prompt_template_key' => ['sometimes', 'nullable', 'string', 'max:190'],
        ];
    }

    public function toData(): QueuePostTitleExcerptRefinementData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return new QueuePostTitleExcerptRefinementData(
            instructions: isset($validated['instructions']) ? (string) $validated['instructions'] : null,
            promptTemplateKey: $validated['prompt_template_key'] ?? null,
        );
    }
}
