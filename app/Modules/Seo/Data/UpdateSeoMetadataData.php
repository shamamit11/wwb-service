<?php

namespace App\Modules\Seo\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateSeoMetadataData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $schemaPayload
     */
    public function __construct(
        public ?string $metaTitle,
        public ?string $metaDescription,
        public ?string $canonicalUrl,
        public bool $robotsIndex,
        public bool $robotsFollow,
        public ?string $ogTitle,
        public ?string $ogDescription,
        public ?int $ogImageMediaId,
        public ?string $schemaType,
        public ?array $schemaPayload,
        public ?string $focusKeyword,
    ) {}
}
