<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Homepage;
use App\Modules\Homepage\Data\UpdateHomepageData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomepageRequest extends FormRequest
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
            'hero' => ['required', 'array'],
            'hero.eyebrow' => ['nullable', 'string', 'max:120'],
            'hero.title' => ['nullable', 'string', 'max:255'],
            'hero.description' => ['nullable', 'string', 'max:2000'],
            'hero.primary_cta_label' => ['nullable', 'string', 'max:120'],
            'hero.primary_cta_url' => ['nullable', 'url', 'max:500'],
            'hero.secondary_cta_label' => ['nullable', 'string', 'max:120'],
            'hero.secondary_cta_url' => ['nullable', 'url', 'max:500'],
            'hero.media_url' => ['nullable', 'url', 'max:500'],
            'hero.media_alt' => ['nullable', 'string', 'max:255'],

            'featured_editorial' => ['required', 'array'],
            'featured_editorial.title' => ['nullable', 'string', 'max:255'],
            'featured_editorial.description' => ['nullable', 'string', 'max:2000'],
            'featured_editorial.mode' => ['required', 'string', Rule::in(Homepage::SECTION_MODES)],
            'featured_editorial.post_ids' => ['present', 'nullable', 'array'],
            'featured_editorial.post_ids.*' => ['integer', 'distinct', 'exists:posts,id'],
            'featured_editorial.category_ids' => ['present', 'nullable', 'array'],
            'featured_editorial.category_ids.*' => ['integer', 'distinct', 'exists:categories,id'],
            'featured_editorial.limit' => ['present', 'nullable', 'integer', 'min:1', 'max:24'],

            'guide_section' => ['required', 'array'],
            'guide_section.title' => ['nullable', 'string', 'max:255'],
            'guide_section.description' => ['nullable', 'string', 'max:2000'],
            'guide_section.mode' => ['required', 'string', Rule::in(Homepage::SECTION_MODES)],
            'guide_section.post_ids' => ['present', 'nullable', 'array'],
            'guide_section.post_ids.*' => ['integer', 'distinct', 'exists:posts,id'],
            'guide_section.category_ids' => ['present', 'nullable', 'array'],
            'guide_section.category_ids.*' => ['integer', 'distinct', 'exists:categories,id'],
            'guide_section.limit' => ['present', 'nullable', 'integer', 'min:1', 'max:24'],

            'topic_section' => ['required', 'array'],
            'topic_section.title' => ['nullable', 'string', 'max:255'],
            'topic_section.description' => ['nullable', 'string', 'max:2000'],
            'topic_section.category_ids' => ['required', 'array'],
            'topic_section.category_ids.*' => ['integer', 'distinct', 'exists:categories,id'],

            'promo_section' => ['required', 'array'],
            'promo_section.enabled' => ['required', 'boolean'],
            'promo_section.eyebrow' => ['nullable', 'string', 'max:120'],
            'promo_section.title' => ['nullable', 'string', 'max:255'],
            'promo_section.description' => ['nullable', 'string', 'max:2000'],
            'promo_section.bullet_points' => ['required', 'array'],
            'promo_section.bullet_points.*' => ['string', 'max:255'],
            'promo_section.primary_cta_label' => ['nullable', 'string', 'max:120'],
            'promo_section.primary_cta_url' => ['nullable', 'url', 'max:500'],
            'promo_section.stats' => ['required', 'array'],
            'promo_section.stats.*.label' => ['required', 'string', 'max:120'],
            'promo_section.stats.*.value' => ['required', 'string', 'max:120'],

            'newsletter_section' => ['required', 'array'],
            'newsletter_section.enabled' => ['required', 'boolean'],
            'newsletter_section.title' => ['nullable', 'string', 'max:255'],
            'newsletter_section.description' => ['nullable', 'string', 'max:2000'],

            'seo' => ['required', 'array'],
            'seo.meta_title' => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:320'],
        ];
    }

    public function toData(int $userId): UpdateHomepageData
    {
        /** @var array{hero:array<string,mixed>,featured_editorial:array<string,mixed>,guide_section:array<string,mixed>,topic_section:array<string,mixed>,promo_section:array<string,mixed>,newsletter_section:array<string,mixed>,seo:array<string,mixed>} $validated */
        $validated = $this->validated();

        return new UpdateHomepageData(
            updatedByUserId: $userId,
            hero: $this->normalizeHero($validated['hero']),
            featuredEditorial: $this->normalizeCollectionSection($validated['featured_editorial']),
            guideSection: $this->normalizeCollectionSection($validated['guide_section']),
            topicSection: $this->normalizeTopicSection($validated['topic_section']),
            promoSection: $this->normalizePromoSection($validated['promo_section']),
            newsletterSection: $this->normalizeNewsletterSection($validated['newsletter_section']),
            seo: $this->normalizeSeo($validated['seo']),
        );
    }

    /**
     * @param  array<string, mixed>  $hero
     * @return array<string, mixed>
     */
    private function normalizeHero(array $hero): array
    {
        return [
            'eyebrow' => $hero['eyebrow'] ?? null,
            'title' => $hero['title'] ?? null,
            'description' => $hero['description'] ?? null,
            'primary_cta_label' => $hero['primary_cta_label'] ?? null,
            'primary_cta_url' => $hero['primary_cta_url'] ?? null,
            'secondary_cta_label' => $hero['secondary_cta_label'] ?? null,
            'secondary_cta_url' => $hero['secondary_cta_url'] ?? null,
            'media_url' => $hero['media_url'] ?? null,
            'media_alt' => $hero['media_alt'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>
     */
    private function normalizeCollectionSection(array $section): array
    {
        return [
            'title' => $section['title'] ?? null,
            'description' => $section['description'] ?? null,
            'mode' => $section['mode'],
            'post_ids' => array_values($section['post_ids'] ?? []),
            'category_ids' => $section['category_ids'] === null ? null : array_values($section['category_ids'] ?? []),
            'limit' => $section['limit'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>
     */
    private function normalizeTopicSection(array $section): array
    {
        return [
            'title' => $section['title'] ?? null,
            'description' => $section['description'] ?? null,
            'category_ids' => array_values($section['category_ids']),
        ];
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>
     */
    private function normalizePromoSection(array $section): array
    {
        return [
            'enabled' => (bool) $section['enabled'],
            'eyebrow' => $section['eyebrow'] ?? null,
            'title' => $section['title'] ?? null,
            'description' => $section['description'] ?? null,
            'bullet_points' => array_values($section['bullet_points']),
            'primary_cta_label' => $section['primary_cta_label'] ?? null,
            'primary_cta_url' => $section['primary_cta_url'] ?? null,
            'stats' => array_map(static fn (array $stat): array => [
                'label' => $stat['label'],
                'value' => $stat['value'],
            ], array_values($section['stats'])),
        ];
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>
     */
    private function normalizeNewsletterSection(array $section): array
    {
        return [
            'enabled' => (bool) $section['enabled'],
            'title' => $section['title'] ?? null,
            'description' => $section['description'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $seo
     * @return array<string, mixed>
     */
    private function normalizeSeo(array $seo): array
    {
        return [
            'meta_title' => $seo['meta_title'] ?? null,
            'meta_description' => $seo['meta_description'] ?? null,
        ];
    }
}
