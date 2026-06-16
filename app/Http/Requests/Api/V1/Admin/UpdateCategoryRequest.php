<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Category;
use App\Modules\Categories\Data\UpdateCategoryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        /** @var Category $category */
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', Rule::notIn([$category->id])],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function toData(int $userId): UpdateCategoryData
    {
        /** @var array{name:string,slug?:string|null,description?:string|null,parent_id?:int|null,is_active?:bool,sort_order?:int|null} $validated */
        $validated = $this->validated();

        return new UpdateCategoryData(
            parentId: $validated['parent_id'] ?? null,
            updatedByUserId: $userId,
            name: $validated['name'],
            slug: $validated['slug'] ?? '',
            description: $validated['description'] ?? null,
            isActive: $validated['is_active'] ?? true,
            sortOrder: $validated['sort_order'] ?? 0,
        );
    }
}
