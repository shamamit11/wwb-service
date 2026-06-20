<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\SiteSettings\Data\UpdateSiteSettingsData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingsRequest extends FormRequest
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
            'footer' => ['required', 'array'],
            'footer.brand_name' => ['nullable', 'string', 'max:120'],
            'footer.description' => ['nullable', 'string', 'max:2000'],
            'footer.social_links' => ['required', 'array'],
            'footer.social_links.*.label' => ['required', 'string', 'max:80'],
            'footer.social_links.*.url' => ['required', 'string', 'max:500'],
            'footer.social_links.*.icon' => ['nullable', 'string', 'max:80'],
            'footer.legal_links' => ['required', 'array'],
            'footer.legal_links.*.label' => ['required', 'string', 'max:120'],
            'footer.legal_links.*.slug' => ['nullable', 'string', 'max:190'],
            'footer.legal_links.*.url' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function toData(int $userId): UpdateSiteSettingsData
    {
        /** @var array{footer:array<string,mixed>} $validated */
        $validated = $this->validated();

        return new UpdateSiteSettingsData(
            updatedByUserId: $userId,
            footer: [
                'brand_name' => $validated['footer']['brand_name'] ?? null,
                'description' => $validated['footer']['description'] ?? null,
                'social_links' => array_map(static fn (array $link): array => [
                    'label' => $link['label'],
                    'url' => $link['url'],
                    'icon' => $link['icon'] ?? null,
                ], array_values($validated['footer']['social_links'] ?? [])),
                'legal_links' => array_map(static fn (array $link): array => [
                    'label' => $link['label'],
                    'slug' => $link['slug'] ?? null,
                    'url' => $link['url'] ?? null,
                ], array_values($validated['footer']['legal_links'] ?? [])),
            ],
        );
    }
}
