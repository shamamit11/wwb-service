<?php

namespace App\Modules\Posts\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class PostBlockPayloadData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $content
     */
    public function __construct(
        public string $blockType,
        public int $sortOrder,
        public array $content,
        public ?int $sourceTemplateBlockId = null,
    ) {}
}
