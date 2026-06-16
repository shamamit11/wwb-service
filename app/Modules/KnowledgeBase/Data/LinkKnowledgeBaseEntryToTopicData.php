<?php

namespace App\Modules\KnowledgeBase\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class LinkKnowledgeBaseEntryToTopicData extends DataTransferObject
{
    public function __construct(
        public int $topicId,
    ) {}
}
