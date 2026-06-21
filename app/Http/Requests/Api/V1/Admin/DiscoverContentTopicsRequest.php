<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Ai\Data\DiscoverContentTopicsData;
use Illuminate\Foundation\Http\FormRequest;

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
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'count' => ['sometimes', 'integer', 'min:1', 'max:25'],
            'audience' => ['sometimes', 'nullable', 'string', 'max:255'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    public function toData(): DiscoverContentTopicsData
    {
        /** @var array{category_id:int,count?:int,audience?:string|null,metadata?:array<string,mixed>} $validated */
        $validated = $this->validated();

        $metadata = is_array($validated['metadata'] ?? null)
            ? $validated['metadata']
            : [];

        $metadata['trigger'] = 'admin_api';

        return new DiscoverContentTopicsData(
            categoryId: $validated['category_id'],
            count: isset($validated['count']) ? (int) $validated['count'] : 10,
            audience: $validated['audience'] ?? null,
            metadata: $metadata,
        );
    }
}
