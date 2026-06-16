<?php

namespace App\Modules\Media\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateMediaMetadataData extends DataTransferObject
{
    public function __construct(
        public ?string $altText,
        public ?string $caption,
        public string $sourceType,
        public ?string $sourceUrl,
        public ?string $attributionText,
        public ?array $metadata = null,
    ) {}
}
