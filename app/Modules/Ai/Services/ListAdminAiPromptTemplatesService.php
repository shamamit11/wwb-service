<?php

namespace App\Modules\Ai\Services;

use App\Modules\Ai\Data\AiPromptTemplateFiltersData;
use App\Modules\Ai\Repositories\AiPromptTemplateRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminAiPromptTemplatesService
{
    public function __construct(
        private readonly AiPromptTemplateRepository $templates,
    ) {}

    public function handle(AiPromptTemplateFiltersData $filters): Collection
    {
        return $this->templates->search($filters);
    }
}
