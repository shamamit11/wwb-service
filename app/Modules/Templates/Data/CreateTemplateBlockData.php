<?php

namespace App\Modules\Templates\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateTemplateBlockData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $settings
     */
    public function __construct(
        public string $blockType,
        public int $sortOrder,
        public ?string $label = null,
        public ?string $defaultMarkdown = null,
        public ?array $settings = null,
        public bool $isRequired = false,
    ) {}
}
