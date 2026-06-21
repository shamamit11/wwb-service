<?php

namespace App\Infrastructure\News\Data;

final readonly class ExtractedNewsContentData
{
    /**
     * @param  array<string, mixed>|null  $facts
     * @param  array<string, mixed>|null  $entities
     * @param  array<string, mixed>|null  $claims
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public ?string $contentMarkdown,
        public ?string $contentText,
        public ?string $excerpt,
        public ?array $facts = null,
        public ?array $entities = null,
        public ?array $claims = null,
        public ?array $metadata = null,
    ) {}
}
