<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\ContentTopic;
use App\Modules\Ai\Data\DiscoverContentTopicsData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiscoverContentTopicsRequest extends FormRequest
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
            'cluster' => ['required', 'string', Rule::in(ContentTopic::CLUSTERS)],
            'count' => ['sometimes', 'integer', 'min:1', 'max:25'],
            'audience' => ['sometimes', 'nullable', 'string', 'max:255'],
            'prompt_template_key' => ['sometimes', 'nullable', 'string', 'max:190'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    public function toData(): DiscoverContentTopicsData
    {
        /** @var array{cluster:string,count?:int,audience?:string|null,prompt_template_key?:string|null,metadata?:array<string,mixed>} $validated */
        $validated = $this->validated();

        $metadata = is_array($validated['metadata'] ?? null)
            ? $validated['metadata']
            : [];

        $metadata['trigger'] = 'admin_api';

        return new DiscoverContentTopicsData(
            cluster: $validated['cluster'],
            count: isset($validated['count']) ? (int) $validated['count'] : 10,
            audience: $validated['audience'] ?? null,
            promptTemplateKey: $validated['prompt_template_key'] ?? null,
            metadata: $metadata,
        );
    }
}
