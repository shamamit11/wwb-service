<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Media\Data\UploadMediaData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class BatchStoreMediaRequest extends FormRequest
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
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif,svg,pdf'],
            'source_type' => ['required', 'in:uploaded,ai_generated,stock'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'attribution_text' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return list<UploadMediaData>
     */
    public function toData(?int $uploadedByUserId): array
    {
        /** @var array{source_type:string,source_url?:string|null,attribution_text?:string|null} $validated */
        $validated = $this->validated();
        /** @var array<int, UploadedFile> $files */
        $files = $this->file('files', []);

        return array_map(function (UploadedFile $file) use ($uploadedByUserId, $validated): UploadMediaData {
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
                sourceType: $validated['source_type'],
                sourceUrl: $validated['source_url'] ?? null,
                attributionText: $validated['attribution_text'] ?? null,
            );
        }, $files);
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
