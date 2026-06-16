<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Tag;
use App\Modules\Tags\Data\UpdateTagData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
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
        /** @var Tag $tag */
        $tag = $this->route('tag');

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('tags', 'slug')->ignore($tag->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): UpdateTagData
    {
        /** @var array{name:string,slug?:string|null,description?:string|null,is_active?:bool} $validated */
        $validated = $this->validated();

        return new UpdateTagData(
            name: $validated['name'],
            slug: $validated['slug'] ?? '',
            description: $validated['description'] ?? null,
            isActive: $validated['is_active'] ?? true,
        );
    }
}
