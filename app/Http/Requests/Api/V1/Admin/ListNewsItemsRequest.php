<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\NewsItem;
use App\Models\NewsItemRoute;
use App\Models\NewsItemScore;
use App\Modules\News\Data\NewsItemFiltersData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListNewsItemsRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::in(NewsItem::STATUSES)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'decision' => ['nullable', 'string', Rule::in(NewsItemScore::DECISIONS)],
            'route' => ['nullable', 'string', Rule::in(NewsItemRoute::ROUTES)],
            'sort' => ['nullable', 'string', Rule::in([
                'published_at',
                '-published_at',
                'discovered_at',
                '-discovered_at',
                'created_at',
                '-created_at',
                'updated_at',
                '-updated_at',
                'title',
                '-title',
            ])],
        ];
    }

    public function toData(): NewsItemFiltersData
    {
        /** @var array{search?:string|null,status?:string|null,category_id?:int|null,decision?:string|null,route?:string|null,sort?:string|null} $validated */
        $validated = $this->validated();

        return new NewsItemFiltersData(
            search: $validated['search'] ?? null,
            status: $validated['status'] ?? null,
            categoryId: $validated['category_id'] ?? null,
            decision: $validated['decision'] ?? null,
            route: $validated['route'] ?? null,
            sort: $validated['sort'] ?? '-published_at',
        );
    }
}
