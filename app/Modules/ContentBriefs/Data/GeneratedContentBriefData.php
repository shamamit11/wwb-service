<?php

namespace App\Modules\ContentBriefs\Data;

use App\Models\ContentBrief;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class GeneratedContentBriefData extends DataTransferObject
{
    public function __construct(
        public ContentBrief $brief,
        public bool $wasCreated,
    ) {}
}
