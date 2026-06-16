<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Categories\Data\CreateCategoryData;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function toData(int $userId): CreateCategoryData
    {
        /** @var array{name:string,slug?:string|null,description?:string|null,parent_id?:int|null,is_active?:bool,sort_order?:int|null} $validated */
        $validated = $this->validated();

        return new CreateCategoryData(
            parentId: $validated['parent_id'] ?? null,
            createdByUserId: $userId,
            updatedByUserId: $userId,
            name: $validated['name'],
            slug: $validated['slug'] ?? '',
            description: $validated['description'] ?? null,
            isActive: $validated['is_active'] ?? true,
            sortOrder: $validated['sort_order'] ?? 0,
        );
    }
}
