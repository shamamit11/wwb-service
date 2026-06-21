<?php

namespace App\Modules\Ai\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class QueuePostMetadataSuggestionData extends DataTransferObject
{
    public function __construct(
        public ?string $instructions = null,
    ) {}
}
