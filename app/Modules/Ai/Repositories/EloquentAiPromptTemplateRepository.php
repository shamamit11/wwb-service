<?php

namespace App\Modules\Ai\Repositories;

use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use App\Modules\Ai\Data\AiPromptTemplateFiltersData;
use App\Modules\Ai\Data\CreateAiPromptTemplateData;
use App\Modules\Ai\Data\CreateAiPromptTemplateVersionData;
use App\Modules\Ai\Data\UpdateAiPromptTemplateData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentAiPromptTemplateRepository implements AiPromptTemplateRepository
{
    public function create(CreateAiPromptTemplateData $data): AiPromptTemplate
    {
        return DB::transaction(function () use ($data): AiPromptTemplate {
            $template = AiPromptTemplate::query()->create([
                'name' => $data->name,
                'key' => $data->key,
                'type' => $data->type,
                'description' => $data->description,
                'status' => $data->status,
            ]);

            $version = $template->versions()->create([
                'version' => 1,
                'system_prompt' => $data->initialVersion->systemPrompt,
                'user_prompt' => $data->initialVersion->userPrompt,
                'output_schema' => $data->initialVersion->outputSchema,
                'variables' => array_values(array_unique($data->initialVersion->variables)),
                'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
            ]);

            $template->update([
                'active_version_id' => $version->id,
            ]);

            return $this->refresh($template);
        });
    }

    public function update(AiPromptTemplate $template, UpdateAiPromptTemplateData $data): AiPromptTemplate
    {
        $template->update([
            'name' => $data->name,
            'key' => $data->key,
            'type' => $data->type,
            'description' => $data->description,
            'status' => $data->status,
        ]);

        return $this->refresh($template);
    }

    public function createVersion(AiPromptTemplate $template, CreateAiPromptTemplateVersionData $data): AiPromptTemplateVersion
    {
        $nextVersion = (int) ($template->versions()->max('version') ?? 0) + 1;

        return $template->versions()->create([
            'version' => $nextVersion,
            'system_prompt' => $data->systemPrompt,
            'user_prompt' => $data->userPrompt,
            'output_schema' => $data->outputSchema,
            'variables' => array_values(array_unique($data->variables)),
            'status' => $data->status === AiPromptTemplateVersion::STATUS_ACTIVE
                ? AiPromptTemplateVersion::STATUS_DRAFT
                : $data->status,
        ])->refresh();
    }

    public function activateVersion(AiPromptTemplate $template, AiPromptTemplateVersion $version): AiPromptTemplate
    {
        DB::transaction(function () use ($template, $version): void {
            $template->versions()
                ->where('status', AiPromptTemplateVersion::STATUS_ACTIVE)
                ->update(['status' => AiPromptTemplateVersion::STATUS_ARCHIVED]);

            $version->update([
                'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
            ]);

            $template->update([
                'active_version_id' => $version->id,
                'status' => AiPromptTemplate::STATUS_ACTIVE,
            ]);
        });

        return $this->refresh($template);
    }

    public function findById(int $id): ?AiPromptTemplate
    {
        return AiPromptTemplate::query()
            ->with(['activeVersion', 'versions'])
            ->withCount('versions')
            ->find($id);
    }

    public function findVersionById(AiPromptTemplate $template, int $versionId): ?AiPromptTemplateVersion
    {
        return $template->versions()
            ->whereKey($versionId)
            ->first();
    }

    public function existsByKey(string $key, ?int $ignoreId = null): bool
    {
        return AiPromptTemplate::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('key', $key)
            ->exists();
    }

    /**
     * @return Collection<int, AiPromptTemplate>
     */
    public function search(AiPromptTemplateFiltersData $filters): Collection
    {
        [$sortColumn, $descending] = $this->normalizeSort($filters->sort);

        return AiPromptTemplate::query()
            ->with('activeVersion')
            ->withCount('versions')
            ->when($filters->type, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters->status, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters->search, function ($query, string $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('key', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortColumn, $descending ? 'desc' : 'asc')
            ->orderByDesc('id')
            ->get();
    }

    private function refresh(AiPromptTemplate $template): AiPromptTemplate
    {
        return $template->refresh()->load(['activeVersion', 'versions'])->loadCount('versions');
    }

    /**
     * @return array{0: string, 1: bool}
     */
    private function normalizeSort(string $sort): array
    {
        $descending = str_starts_with($sort, '-');
        $field = ltrim($sort, '-');
        $allowed = ['name', 'key', 'type', 'created_at', 'updated_at'];

        if (! in_array($field, $allowed, true)) {
            return ['name', false];
        }

        return [$field, $descending];
    }
}
