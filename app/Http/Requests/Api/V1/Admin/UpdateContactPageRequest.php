<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\ContactPage\Data\UpdateContactPageData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContactPageRequest extends FormRequest
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

            'contact_form' => ['required', 'array'],
            'contact_form.eyebrow' => ['nullable', 'string', 'max:120'],
            'contact_form.title' => ['nullable', 'string', 'max:255'],
            'contact_form.description' => ['nullable', 'string', 'max:2000'],
            'contact_form.submit_label' => ['nullable', 'string', 'max:120'],
            'contact_form.success_message' => ['nullable', 'string', 'max:500'],

            'contact_reasons' => ['required', 'array'],
            'contact_reasons.items' => ['required', 'array'],
            'contact_reasons.items.*.title' => ['required', 'string', 'max:120'],
            'contact_reasons.items.*.description' => ['required', 'string', 'max:500'],

            'seo' => ['required', 'array'],
            'seo.meta_title' => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:320'],
        ];
    }

    public function toData(int $userId): UpdateContactPageData
    {
        /** @var array{hero:array<string,mixed>,contact_form:array<string,mixed>,contact_reasons:array{items:array<int,array{title:string,description:string}>},seo:array<string,mixed>} $validated */
        $validated = $this->validated();

        return new UpdateContactPageData(
            updatedByUserId: $userId,
            hero: [
                'eyebrow' => $validated['hero']['eyebrow'] ?? null,
                'title' => $validated['hero']['title'] ?? null,
                'description' => $validated['hero']['description'] ?? null,
            ],
            contactForm: [
                'eyebrow' => $validated['contact_form']['eyebrow'] ?? null,
                'title' => $validated['contact_form']['title'] ?? null,
                'description' => $validated['contact_form']['description'] ?? null,
                'submit_label' => $validated['contact_form']['submit_label'] ?? null,
                'success_message' => $validated['contact_form']['success_message'] ?? null,
            ],
            contactReasons: [
                'items' => array_map(static fn (array $item): array => [
                    'title' => $item['title'],
                    'description' => $item['description'],
                ], array_values($validated['contact_reasons']['items'])),
            ],
            seo: [
                'meta_title' => $validated['seo']['meta_title'] ?? null,
                'meta_description' => $validated['seo']['meta_description'] ?? null,
            ],
        );
    }
}
