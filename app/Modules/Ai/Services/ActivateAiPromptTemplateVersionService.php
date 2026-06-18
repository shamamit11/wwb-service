<?php

namespace App\Modules\Ai\Services;

use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use App\Modules\Ai\Repositories\AiPromptTemplateRepository;

class ActivateAiPromptTemplateVersionService
{
    public function __construct(
        private readonly AiPromptTemplateRepository $templates,
    ) {}

    public function handle(AiPromptTemplate $template, int $versionId): AiPromptTemplate
    {
        $version = $this->templates->findVersionById($template, $versionId)
            ?? throw (new AiPromptTemplateVersion)->newQuery()->whereKey($versionId)->firstOrFail();

        return $this->templates->activateVersion($template, $version);
    }
}
