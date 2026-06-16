<?php

namespace App\Modules\Media\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateMediaData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $ulid,
        public ?int $uploadedByUserId,
        public ?int $generatedByAiJobId,
        public string $storageProvider,
        public string $bucketName,
        public string $objectKey,
        public string $originalFilename,
        public string $mimeType,
        public ?string $extension,
        public int $fileSizeBytes,
        public ?string $checksumSha256,
        public ?int $width,
        public ?int $height,
        public ?string $altText,
        public ?string $caption,
        public string $sourceType,
        public ?string $sourceUrl,
        public ?string $attributionText,
        public string $status,
        public ?array $metadata,
    ) {}
}
