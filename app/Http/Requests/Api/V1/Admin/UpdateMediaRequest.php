<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Media\Data\UpdateMediaMetadataData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaRequest extends FormRequest
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
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
            'source_type' => ['required', 'in:uploaded,ai_generated,stock'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'attribution_text' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toData(): UpdateMediaMetadataData
    {
        /** @var array{alt_text?:string|null,caption?:string|null,source_type:string,source_url?:string|null,attribution_text?:string|null} $validated */
        $validated = $this->validated();

        return new UpdateMediaMetadataData(
            altText: $validated['alt_text'] ?? null,
            caption: $validated['caption'] ?? null,
            sourceType: $validated['source_type'],
            sourceUrl: $validated['source_url'] ?? null,
            attributionText: $validated['attribution_text'] ?? null,
        );
    }
}
