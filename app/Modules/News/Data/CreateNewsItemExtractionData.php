<?php

namespace App\Modules\News\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateNewsItemExtractionData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $factsJson
     * @param  array<string, mixed>|null  $entitiesJson
     * @param  array<string, mixed>|null  $claimsJson
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $extractor,
        public ?string $contentMarkdown,
        public ?string $contentText,
        public ?string $excerpt,
        public ?array $factsJson,
        public ?array $entitiesJson,
        public ?array $claimsJson,
        public ?string $extractedAt,
        public ?array $metadata = null,
    ) {}
}
