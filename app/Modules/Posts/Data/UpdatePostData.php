<?php

namespace App\Modules\Posts\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdatePostData extends DataTransferObject
{
    /**
     * @param  list<int>  $tagIds
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public int $authorUserId,
        public int $categoryId,
        public ?int $templateId,
        public ?int $featuredMediaId,
        public string $title,
        public string $slug,
        public ?string $excerpt,
        public string $status,
        public string $visibility,
        public ?string $publishedAt,
        public ?string $scheduledFor,
        public int $contentVersion,
        public ?int $readingTimeMinutes,
        public ?int $wordCount,
        public bool $isFeatured,
        public ?array $meta,
        public array $tagIds = [],
    ) {}
}
