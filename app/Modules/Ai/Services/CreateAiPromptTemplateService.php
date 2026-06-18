<?php

namespace App\Modules\Ai\Services;

use App\Models\AiPromptTemplate;
use App\Modules\Ai\Data\CreateAiPromptTemplateData;
use App\Modules\Ai\Repositories\AiPromptTemplateRepository;

class CreateAiPromptTemplateService
{
    public function __construct(
        private readonly AiPromptTemplateRepository $templates,
    ) {}

    public function handle(CreateAiPromptTemplateData $data): AiPromptTemplate
    {
        return $this->templates->create($data);
    }
}
