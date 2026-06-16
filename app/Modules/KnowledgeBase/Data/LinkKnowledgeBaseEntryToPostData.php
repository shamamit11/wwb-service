<?php

namespace App\Modules\KnowledgeBase\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class LinkKnowledgeBaseEntryToPostData extends DataTransferObject
{
    public function __construct(
        public int $postId,
    ) {}
}
