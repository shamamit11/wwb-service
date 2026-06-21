<?php

namespace App\Infrastructure\News\Data;

final readonly class DiscoveredNewsArticleData
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public ?string $externalId,
        public string $provider,
        public ?string $publisherName,
        public ?string $publisherDomain,
        public string $title,
        public string $url,
        public ?string $canonicalUrl,
        public ?string $description,
        public ?string $author,
        public ?string $language,
        public ?string $country,
        public ?string $publishedAt,
        public ?array $metadata = null,
    ) {}
}
