<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\AiJob;
use App\Modules\Ai\Data\AiJobFiltersData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAiJobsRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::in(AiJob::STATUSES)],
            'type' => ['nullable', 'string', 'max:120'],
            'entity_type' => ['nullable', 'string', 'max:120'],
            'entity_id' => ['nullable', 'integer', 'min:1'],
            'sort' => ['nullable', 'string', Rule::in([
                'attempts',
                '-attempts',
                'created_at',
                '-created_at',
                'updated_at',
                '-updated_at',
                'started_at',
                '-started_at',
                'completed_at',
                '-completed_at',
                'failed_at',
                '-failed_at',
            ])],
        ];
    }

    public function toData(): AiJobFiltersData
    {
        /** @var array{status?:string|null,type?:string|null,entity_type?:string|null,entity_id?:int|null,sort?:string|null} $validated */
        $validated = $this->validated();

        return new AiJobFiltersData(
            status: $validated['status'] ?? null,
            type: $validated['type'] ?? null,
            entityType: $validated['entity_type'] ?? null,
            entityId: $validated['entity_id'] ?? null,
            sort: $validated['sort'] ?? '-created_at',
        );
    }
}
