<?php

namespace App\Modules\Media\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UploadMediaData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $originalFilename,
        public string $mimeType,
        public string $contents,
        public ?int $uploadedByUserId = null,
        public ?int $generatedByAiJobId = null,
        public ?string $extension = null,
        public ?int $fileSizeBytes = null,
        public ?string $checksumSha256 = null,
        public ?int $width = null,
        public ?int $height = null,
        public ?string $altText = null,
        public ?string $caption = null,
        public string $sourceType = 'uploaded',
        public ?string $sourceUrl = null,
        public ?string $attributionText = null,
        public ?array $metadata = null,
    ) {}
}
