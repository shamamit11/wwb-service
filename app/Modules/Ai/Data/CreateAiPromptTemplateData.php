<?php

namespace App\Modules\Ai\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateAiPromptTemplateData extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $key,
        public string $type,
        public ?string $description,
        public string $status,
        public CreateAiPromptTemplateVersionData $initialVersion,
    ) {}
}
