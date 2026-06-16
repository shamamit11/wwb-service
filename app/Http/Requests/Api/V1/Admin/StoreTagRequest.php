<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Tags\Data\CreateTagData;
use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:140', 'unique:tags,slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): CreateTagData
    {
        /** @var array{name:string,slug?:string|null,description?:string|null,is_active?:bool} $validated */
        $validated = $this->validated();

        return new CreateTagData(
            name: $validated['name'],
            slug: $validated['slug'] ?? '',
            description: $validated['description'] ?? null,
            isActive: $validated['is_active'] ?? true,
        );
    }
}
