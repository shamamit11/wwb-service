<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\ContentBrief;
use App\Modules\ContentBriefs\Data\UpdateContentBriefData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentBriefRequest extends FormRequest
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
        /** @var ContentBrief $contentBrief */
        $contentBrief = $this->route('contentBrief');

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:190', Rule::unique('content_briefs', 'slug')->ignore($contentBrief->id)],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string'],
            'primary_keyword' => ['sometimes', 'nullable', 'string', 'max:255'],
            'secondary_keywords' => ['sometimes', 'array'],
            'secondary_keywords.*' => ['string', 'max:255'],
            'search_intent' => ['sometimes', 'nullable', 'string', 'max:255'],
            'outline' => ['sometimes', 'array'],
            'outline.*' => ['array'],
            'headings' => ['sometimes', 'array'],
            'headings.*' => ['string', 'max:255'],
            'faq_suggestions' => ['sometimes', 'array'],
            'faq_suggestions.*' => ['array'],
            'internal_link_suggestions' => ['sometimes', 'array'],
            'internal_link_suggestions.*' => ['array'],
            'image_suggestions' => ['sometimes', 'array'],
            'image_suggestions.*' => ['array'],
            'status' => ['sometimes', 'string', Rule::in([
                ContentBrief::STATUS_DRAFT,
                ContentBrief::STATUS_REJECTED,
                ContentBrief::STATUS_USED,
            ])],
        ];
    }

    public function toData(): UpdateContentBriefData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return new UpdateContentBriefData(
            title: $validated['title'] ?? null,
            slug: $validated['slug'] ?? null,
            metaTitle: $validated['meta_title'] ?? null,
            metaDescription: $validated['meta_description'] ?? null,
            primaryKeyword: $validated['primary_keyword'] ?? null,
            secondaryKeywords: isset($validated['secondary_keywords']) ? array_values($validated['secondary_keywords']) : null,
            searchIntent: $validated['search_intent'] ?? null,
            outline: isset($validated['outline']) ? array_values($validated['outline']) : null,
            headings: isset($validated['headings']) ? array_values($validated['headings']) : null,
            faqSuggestions: isset($validated['faq_suggestions']) ? array_values($validated['faq_suggestions']) : null,
            internalLinkSuggestions: isset($validated['internal_link_suggestions']) ? array_values($validated['internal_link_suggestions']) : null,
            imageSuggestions: isset($validated['image_suggestions']) ? array_values($validated['image_suggestions']) : null,
            status: $validated['status'] ?? null,
        );
    }
}
