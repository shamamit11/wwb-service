<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Media\Data\UploadMediaData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreMediaRequest extends FormRequest
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
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif,svg,pdf'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
            'source_type' => ['required', 'in:uploaded,ai_generated,stock'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'attribution_text' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toData(?int $uploadedByUserId): UploadMediaData
    {
        /** @var UploadedFile $file */
        $file = $this->file('file');
        /** @var array{alt_text?:string|null,caption?:string|null,source_type:string,source_url?:string|null,attribution_text?:string|null} $validated */
        $validated = $this->validated();
        [$width, $height] = $this->dimensions($file);

        return new UploadMediaData(
            originalFilename: $file->getClientOriginalName(),
            mimeType: $file->getMimeType() ?: 'application/octet-stream',
            contents: file_get_contents($file->getRealPath()) ?: '',
            uploadedByUserId: $uploadedByUserId,
            extension: $file->getClientOriginalExtension() ?: null,
            fileSizeBytes: $file->getSize(),
            width: $width,
            height: $height,
            altText: $validated['alt_text'] ?? null,
            caption: $validated['caption'] ?? null,
            sourceType: $validated['source_type'],
            sourceUrl: $validated['source_url'] ?? null,
            attributionText: $validated['attribution_text'] ?? null,
        );
    }

    /**
     * @return array{0:int|null,1:int|null}
     */
    private function dimensions(UploadedFile $file): array
    {
        $imageInfo = @getimagesize($file->getRealPath());

        if (! is_array($imageInfo)) {
            return [null, null];
        }

        return [
            (int) $imageInfo[0],
            (int) $imageInfo[1],
        ];
    }
}
