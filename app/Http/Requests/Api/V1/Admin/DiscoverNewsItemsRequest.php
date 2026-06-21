<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DiscoverNewsItemsRequest extends FormRequest
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
            'limit' => ['nullable', 'integer', 'min:1', 'max:25'],
            'sync' => ['nullable', 'boolean'],
        ];
    }

    public function categoryId(): int
    {
        return (int) $this->validated('category_id');
    }

    public function limit(): int
    {
        return max(1, (int) ($this->validated('limit') ?? 10));
    }

    public function sync(): bool
    {
        return (bool) ($this->validated('sync') ?? false);
    }
}
