<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\AboutPage\Data\UpdateAboutPageData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAboutPageRequest extends FormRequest
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
            'hero.media_url' => ['nullable', 'url', 'max:500'],
            'hero.media_alt' => ['nullable', 'string', 'max:255'],

            'mission_section' => ['required', 'array'],
            'mission_section.title' => ['nullable', 'string', 'max:255'],
            'mission_section.description' => ['nullable', 'string', 'max:3000'],
            'mission_section.quote' => ['nullable', 'string', 'max:1000'],

            'stats_section' => ['required', 'array'],
            'stats_section.items' => ['required', 'array'],
            'stats_section.items.*.label' => ['required', 'string', 'max:120'],
            'stats_section.items.*.value' => ['required', 'string', 'max:120'],

            'values_section' => ['required', 'array'],
            'values_section.title' => ['nullable', 'string', 'max:255'],
            'values_section.items' => ['required', 'array'],
            'values_section.items.*.icon' => ['nullable', 'string', 'max:80'],
            'values_section.items.*.title' => ['required', 'string', 'max:120'],
            'values_section.items.*.description' => ['required', 'string', 'max:1000'],

            'team_section' => ['required', 'array'],
            'team_section.title' => ['nullable', 'string', 'max:255'],
            'team_section.description' => ['nullable', 'string', 'max:2000'],
            'team_section.primary_cta_label' => ['nullable', 'string', 'max:120'],
            'team_section.primary_cta_url' => ['nullable', 'url', 'max:500'],
            'team_section.members' => ['required', 'array'],
            'team_section.members.*.name' => ['required', 'string', 'max:120'],
            'team_section.members.*.role' => ['required', 'string', 'max:160'],
            'team_section.members.*.image_url' => ['nullable', 'url', 'max:500'],
            'team_section.members.*.image_alt' => ['nullable', 'string', 'max:255'],

            'seo' => ['required', 'array'],
            'seo.meta_title' => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:320'],
        ];
    }

    public function toData(int $userId): UpdateAboutPageData
    {
        /** @var array{hero:array<string,mixed>,mission_section:array<string,mixed>,stats_section:array{items:array<int,array{label:string,value:string}>},values_section:array<string,mixed>,team_section:array<string,mixed>,seo:array<string,mixed>} $validated */
        $validated = $this->validated();

        return new UpdateAboutPageData(
            updatedByUserId: $userId,
            hero: $this->normalizeHero($validated['hero']),
            missionSection: $this->normalizeMissionSection($validated['mission_section']),
            statsSection: $this->normalizeStatsSection($validated['stats_section']),
            valuesSection: $this->normalizeValuesSection($validated['values_section']),
            teamSection: $this->normalizeTeamSection($validated['team_section']),
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
            'media_url' => $hero['media_url'] ?? null,
            'media_alt' => $hero['media_alt'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $missionSection
     * @return array<string, mixed>
     */
    private function normalizeMissionSection(array $missionSection): array
    {
        return [
            'title' => $missionSection['title'] ?? null,
            'description' => $missionSection['description'] ?? null,
            'quote' => $missionSection['quote'] ?? null,
        ];
    }

    /**
     * @param  array{items:array<int,array{label:string,value:string}>}  $statsSection
     * @return array<string, mixed>
     */
    private function normalizeStatsSection(array $statsSection): array
    {
        return [
            'items' => array_map(static fn (array $item): array => [
                'label' => $item['label'],
                'value' => $item['value'],
            ], array_values($statsSection['items'])),
        ];
    }

    /**
     * @param  array<string, mixed>  $valuesSection
     * @return array<string, mixed>
     */
    private function normalizeValuesSection(array $valuesSection): array
    {
        return [
            'title' => $valuesSection['title'] ?? null,
            'items' => array_map(static fn (array $item): array => [
                'icon' => $item['icon'] ?? null,
                'title' => $item['title'],
                'description' => $item['description'],
            ], array_values($valuesSection['items'])),
        ];
    }

    /**
     * @param  array<string, mixed>  $teamSection
     * @return array<string, mixed>
     */
    private function normalizeTeamSection(array $teamSection): array
    {
        return [
            'title' => $teamSection['title'] ?? null,
            'description' => $teamSection['description'] ?? null,
            'primary_cta_label' => $teamSection['primary_cta_label'] ?? null,
            'primary_cta_url' => $teamSection['primary_cta_url'] ?? null,
            'members' => array_map(static fn (array $member): array => [
                'name' => $member['name'],
                'role' => $member['role'],
                'image_url' => $member['image_url'] ?? null,
                'image_alt' => $member['image_alt'] ?? null,
            ], array_values($teamSection['members'])),
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
