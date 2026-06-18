<?php

namespace App\Modules\Ai\Services;

use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use App\Modules\Ai\Data\CreateAiPromptTemplateVersionData;
use App\Modules\Ai\Repositories\AiPromptTemplateRepository;

class CreateAiPromptTemplateVersionService
{
    public function __construct(
        private readonly AiPromptTemplateRepository $templates,
    ) {}

    public function handle(AiPromptTemplate $template, CreateAiPromptTemplateVersionData $data): AiPromptTemplateVersion
    {
        return $this->templates->createVersion($template, $data);
    }
}
