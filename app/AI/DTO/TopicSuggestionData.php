<?php

namespace App\AI\DTO;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class TopicSuggestionData extends DataTransferObject
{
    /**
     * @param  list<string>  $secondaryKeywords
     */
    public function __construct(
        public string $title,
        public string $slug,
        public string $cluster,
        public ?string $primaryKeyword = null,
        public array $secondaryKeywords = [],
        public ?string $searchIntent = null,
        public ?string $priorityScore = null,
        public ?array $scoreBreakdown = null,
        public ?string $difficultyNote = null,
        public ?string $summary = null,
    ) {}
}
