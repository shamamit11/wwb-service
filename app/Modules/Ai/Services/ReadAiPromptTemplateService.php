<?php

namespace App\Modules\Ai\Services;

use App\Models\AiPromptTemplate;
use App\Modules\Ai\Repositories\AiPromptTemplateRepository;

class ReadAiPromptTemplateService
{
    public function __construct(
        private readonly AiPromptTemplateRepository $templates,
    ) {}

    public function handle(int $id): AiPromptTemplate
    {
        return $this->templates->findById($id)
            ?? throw (new AiPromptTemplate)->newQuery()->whereKey($id)->firstOrFail();
    }
}
