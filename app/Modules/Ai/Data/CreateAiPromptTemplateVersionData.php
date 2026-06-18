<?php

namespace App\Modules\Ai\Data;

use App\Models\AiPromptTemplateVersion;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateAiPromptTemplateVersionData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $outputSchema
     * @param  list<string>  $variables
     */
    public function __construct(
        public string $systemPrompt,
        public string $userPrompt,
        public ?array $outputSchema = null,
        public array $variables = [],
        public string $status = AiPromptTemplateVersion::STATUS_ACTIVE,
    ) {}
}
