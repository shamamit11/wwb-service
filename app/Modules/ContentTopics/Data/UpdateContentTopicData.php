<?php

namespace App\Modules\ContentTopics\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateContentTopicData extends DataTransferObject
{
    /**
     * @param  list<string>  $secondaryKeywords
     */
    public function __construct(
        public int $categoryId,
        public string $title,
        public ?string $slug,
        public string $cluster,
        public ?string $primaryKeyword = null,
        public array $secondaryKeywords = [],
        public ?string $searchIntent = null,
        public ?string $priorityScore = null,
        public ?array $scoreBreakdown = null,
        public ?string $difficultyNote = null,
        public string $source = 'manual',
        public ?string $notes = null,
    ) {}
}
