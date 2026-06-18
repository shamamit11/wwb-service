<?php

namespace App\Modules\Posts\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class PostFiltersData extends DataTransferObject
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?string $visibility = null,
        public ?string $categorySlug = null,
        public ?bool $isFeatured = null,
        public ?int $authorUserId = null,
        public ?bool $isAiGenerated = null,
        public ?int $sourceContentBriefId = null,
        public ?int $sourceContentTopicId = null,
        public ?int $generatedByAiJobId = null,
        public string $sort = '-updated_at',
    ) {}
}
