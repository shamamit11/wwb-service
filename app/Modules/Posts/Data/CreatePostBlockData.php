<?php

namespace App\Modules\Posts\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreatePostBlockData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $settings
     */
    public function __construct(
        public string $blockType,
        public int $sortOrder,
        public ?string $contentMarkdown,
        public ?string $contentHtmlCache,
        public ?string $plainTextCache,
        public ?array $settings,
        public ?int $sourceTemplateBlockId = null,
    ) {}
}
