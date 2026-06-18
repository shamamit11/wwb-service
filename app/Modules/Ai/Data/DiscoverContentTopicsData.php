<?php

namespace App\Modules\Ai\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class DiscoverContentTopicsData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $cluster,
        public int $count = 10,
        public ?string $audience = null,
        public ?string $promptTemplateKey = null,
        public array $metadata = [],
    ) {}
}
