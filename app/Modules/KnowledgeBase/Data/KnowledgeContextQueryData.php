<?php

namespace App\Modules\KnowledgeBase\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class KnowledgeContextQueryData extends DataTransferObject
{
    /**
     * @param  list<string>  $keywords
     * @param  list<string>  $entryTypes
     * @param  array<string, scalar|list<scalar>|null>  $metadataFilters
     */
    public function __construct(
        public ?string $subject = null,
        public array $keywords = [],
        public array $entryTypes = [],
        public array $metadataFilters = [],
        public int $maxEntries = 6,
        public int $maxEntryCharacters = 320,
        public int $maxTotalCharacters = 1800,
        public int $candidatePoolSize = 25,
    ) {}
}
