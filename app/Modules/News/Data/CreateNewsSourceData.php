<?php

namespace App\Modules\News\Data;

use App\Models\NewsSource;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateNewsSourceData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $name,
        public string $slug,
        public string $kind = NewsSource::KIND_PUBLISHER,
        public ?string $baseUrl = null,
        public string $trustScore = '0.00',
        public bool $isActive = true,
        public ?array $metadata = null,
    ) {}
}
