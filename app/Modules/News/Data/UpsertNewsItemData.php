<?php

namespace App\Modules\News\Data;

use App\Models\NewsItem;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpsertNewsItemData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public ?string $externalId,
        public string $provider,
        public ?int $sourceId,
        public ?int $categoryId,
        public ?string $publisherName,
        public string $title,
        public string $normalizedTitle,
        public string $url,
        public ?string $canonicalUrl,
        public ?string $description,
        public ?string $author,
        public ?string $language,
        public ?string $country,
        public ?string $publishedAt,
        public ?string $discoveredAt,
        public string $status = NewsItem::STATUS_DISCOVERED,
        public ?array $metadata = null,
    ) {}
}
