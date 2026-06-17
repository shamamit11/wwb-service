<?php

namespace App\Modules\Pages\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreatePageData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public int $createdByUserId,
        public ?int $updatedByUserId,
        public string $title,
        public string $slug,
        public string $type,
        public string $status,
        public ?string $summary,
        public string $contentMarkdown,
        public string $visibility,
        public ?string $publishedAt,
        public ?string $scheduledFor,
        public ?array $meta,
    ) {}
}
