<?php

namespace App\Modules\Media\Services;

use App\Models\Media;
use App\Modules\Media\Data\CreateMediaData;
use App\Modules\Media\Data\UploadMediaData;
use App\Modules\Media\Repositories\MediaRepository;
use App\Modules\Media\Services\Contracts\MediaStorage;
use App\Modules\Media\Services\Contracts\MediaUploader;
use Illuminate\Support\Str;

class UploadMediaService implements MediaUploader
{
    public function __construct(
        private readonly MediaRepository $media,
        private readonly MediaStorage $storage,
    ) {}

    public function upload(UploadMediaData $data): Media
    {
        $ulid = (string) Str::ulid();
        $extension = $data->extension ?: $this->guessExtension($data->originalFilename);
        $objectKey = $this->objectKey($ulid, $data->originalFilename, $extension);
        $checksum = $data->checksumSha256 ?: hash('sha256', $data->contents);
        $fileSize = $data->fileSizeBytes ?? strlen($data->contents);

        $this->storage->put($objectKey, $data->contents, [
            'visibility' => 'public',
            'ContentType' => $data->mimeType,
        ]);

        return $this->media->create(new CreateMediaData(
            ulid: $ulid,
            uploadedByUserId: $data->uploadedByUserId,
            generatedByAiJobId: $data->generatedByAiJobId,
            storageProvider: 'r2',
            bucketName: (string) config('filesystems.disks.r2.bucket'),
            objectKey: $objectKey,
            originalFilename: $data->originalFilename,
            mimeType: $data->mimeType,
            extension: $extension,
            fileSizeBytes: $fileSize,
            checksumSha256: $checksum,
            width: $data->width,
            height: $data->height,
            altText: $data->altText,
            caption: $data->caption,
            sourceType: $data->sourceType,
            sourceUrl: $data->sourceUrl,
            attributionText: $data->attributionText,
            status: 'ready',
            metadata: $data->metadata,
        ));
    }

    private function objectKey(string $ulid, string $originalFilename, ?string $extension): string
    {
        $baseName = pathinfo($originalFilename, PATHINFO_FILENAME);
        $sanitized = Str::slug($baseName);
        $sanitized = $sanitized !== '' ? $sanitized : 'file';
        $suffix = $extension ? ".{$extension}" : '';

        return now()->format("media/Y/m/{$ulid}-{$sanitized}{$suffix}");
    }

    private function guessExtension(string $originalFilename): ?string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);

        return $extension !== '' ? Str::lower($extension) : null;
    }
}
