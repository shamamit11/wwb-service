<?php

namespace App\Modules\KnowledgeBase\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateKnowledgeBaseEntryData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public int $createdByUserId,
        public ?int $updatedByUserId,
        public string $title,
        public string $slug,
        public string $entryType,
        public string $status,
        public ?string $summary,
        public string $contentMarkdown,
        public ?string $sourceUrl,
        public ?int $featuredMediaId,
        public ?array $metadata,
    ) {}
}
