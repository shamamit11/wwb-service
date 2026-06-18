<?php

namespace App\Modules\Ai\Services;

use App\Models\AiPromptTemplate;
use App\Modules\Ai\Data\UpdateAiPromptTemplateData;
use App\Modules\Ai\Repositories\AiPromptTemplateRepository;

class UpdateAiPromptTemplateService
{
    public function __construct(
        private readonly AiPromptTemplateRepository $templates,
    ) {}

    public function handle(AiPromptTemplate $template, UpdateAiPromptTemplateData $data): AiPromptTemplate
    {
        return $this->templates->update($template, $data);
    }
}
