<?php

namespace App\Http\Requests\Api\V1\Public;

use App\Modules\Posts\Data\PublicPostFiltersData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchPublicPostsRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', Rule::in(['title', '-title', 'published_at', '-published_at', 'updated_at', '-updated_at'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function toData(): PublicPostFiltersData
    {
        /** @var array{q?:string|null,sort?:string|null,per_page?:int|null} $validated */
        $validated = $this->validated();

        return new PublicPostFiltersData(
            search: $validated['q'] ?? null,
            sort: $validated['sort'] ?? '-published_at',
            perPage: $validated['per_page'] ?? 15,
            returnEmptyWhenSearchBlank: true,
        );
    }
}
