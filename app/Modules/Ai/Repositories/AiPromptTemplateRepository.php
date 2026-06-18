<?php

namespace App\Modules\Ai\Repositories;

use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use App\Modules\Ai\Data\AiPromptTemplateFiltersData;
use App\Modules\Ai\Data\CreateAiPromptTemplateData;
use App\Modules\Ai\Data\CreateAiPromptTemplateVersionData;
use App\Modules\Ai\Data\UpdateAiPromptTemplateData;
use Illuminate\Database\Eloquent\Collection;

interface AiPromptTemplateRepository
{
    public function create(CreateAiPromptTemplateData $data): AiPromptTemplate;

    public function update(AiPromptTemplate $template, UpdateAiPromptTemplateData $data): AiPromptTemplate;

    public function createVersion(AiPromptTemplate $template, CreateAiPromptTemplateVersionData $data): AiPromptTemplateVersion;

    public function activateVersion(AiPromptTemplate $template, AiPromptTemplateVersion $version): AiPromptTemplate;

    public function findById(int $id): ?AiPromptTemplate;

    public function findByKey(string $key): ?AiPromptTemplate;

    public function findActiveByType(string $type): ?AiPromptTemplate;

    public function findVersionById(AiPromptTemplate $template, int $versionId): ?AiPromptTemplateVersion;

    public function existsByKey(string $key, ?int $ignoreId = null): bool;

    /**
     * @return Collection<int, AiPromptTemplate>
     */
    public function search(AiPromptTemplateFiltersData $filters): Collection;
}
