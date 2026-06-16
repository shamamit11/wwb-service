<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Seo\Data\UpdateSeoMetadataData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoMetadataRequest extends FormRequest
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
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url', 'max:500'],
            'robots_index' => ['sometimes', 'boolean'],
            'robots_follow' => ['sometimes', 'boolean'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'og_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'schema_type' => ['nullable', 'string', 'max:120'],
            'schema_payload' => ['nullable', 'array'],
            'focus_keyword' => ['nullable', 'string', 'max:190'],
        ];
    }

    public function toData(): UpdateSeoMetadataData
    {
        /** @var array{meta_title?:string|null,meta_description?:string|null,canonical_url?:string|null,robots_index?:bool,robots_follow?:bool,og_title?:string|null,og_description?:string|null,og_image_media_id?:int|null,schema_type?:string|null,schema_payload?:array<string,mixed>|null,focus_keyword?:string|null} $validated */
        $validated = $this->validated();

        return new UpdateSeoMetadataData(
            metaTitle: $validated['meta_title'] ?? null,
            metaDescription: $validated['meta_description'] ?? null,
            canonicalUrl: $validated['canonical_url'] ?? null,
            robotsIndex: $validated['robots_index'] ?? true,
            robotsFollow: $validated['robots_follow'] ?? true,
            ogTitle: $validated['og_title'] ?? null,
            ogDescription: $validated['og_description'] ?? null,
            ogImageMediaId: $validated['og_image_media_id'] ?? null,
            schemaType: $validated['schema_type'] ?? null,
            schemaPayload: $validated['schema_payload'] ?? null,
            focusKeyword: $validated['focus_keyword'] ?? null,
        );
    }
}
