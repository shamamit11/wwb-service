<?php

namespace App\Modules\Ai\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class RenderedPromptData extends DataTransferObject
{
    /**
     * @param  list<string>  $variables
     * @param  list<string>  $missingVariables
     * @param  array<string, mixed>|null  $outputSchema
     */
    public function __construct(
        public string $systemPrompt,
        public string $userPrompt,
        public ?array $outputSchema,
        public array $variables,
        public array $missingVariables,
    ) {}
}
