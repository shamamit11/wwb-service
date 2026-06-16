<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Media\Data\MediaFiltersData;
use Illuminate\Foundation\Http\FormRequest;

class ListMediaRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'source_type' => ['nullable', 'in:uploaded,ai_generated,stock'],
            'mime_type' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:pending,ready,failed,archived'],
            'used' => ['nullable', 'boolean'],
            'is_image' => ['nullable', 'boolean'],
        ];
    }

    public function toData(): MediaFiltersData
    {
        /** @var array{search?:string|null,source_type?:string|null,mime_type?:string|null,status?:string|null,used?:bool|null,is_image?:bool|null} $validated */
        $validated = $this->validated();

        return new MediaFiltersData(
            search: $validated['search'] ?? null,
            sourceType: $validated['source_type'] ?? null,
            mimeType: $validated['mime_type'] ?? null,
            status: $validated['status'] ?? null,
            used: $validated['used'] ?? null,
            isImage: $validated['is_image'] ?? null,
        );
    }
}
