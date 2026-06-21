<?php

namespace App\Modules\ContentBriefs\Data;

use App\Models\ContentBrief;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class ContinueContentBriefToDraftResultData extends DataTransferObject
{
    public function __construct(
        public ContentBrief $brief,
        public string $status,
        public ?int $aiJobId = null,
        public ?int $postId = null,
    ) {}
}
