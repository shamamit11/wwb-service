<?php

namespace App\Modules\Templates\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateTemplateData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $defaultMeta
     * @param  list<CreateTemplateBlockData>  $blocks
     */
    public function __construct(
        public ?int $updatedByUserId,
        public string $name,
        public string $slug,
        public string $templateType,
        public ?string $description,
        public string $status,
        public ?string $defaultExcerptPrompt,
        public ?array $defaultMeta,
        public array $blocks = [],
    ) {}
}
