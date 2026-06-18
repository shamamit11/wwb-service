<?php

namespace App\Modules\Ai\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class QueuePostRewriteData extends DataTransferObject
{
    public function __construct(
        public string $scope,
        public array $targetBlockIds = [],
        public ?string $instructions = null,
        public ?string $promptTemplateKey = null,
    ) {}
}
