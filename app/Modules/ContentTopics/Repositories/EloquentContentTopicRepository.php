<?php

namespace App\Modules\ContentTopics\Repositories;

use App\Models\ContentTopic;
use App\Modules\ContentTopics\Data\ContentTopicFiltersData;
use App\Modules\ContentTopics\Data\ContentTopicStateTransitionData;
use App\Modules\ContentTopics\Data\CreateContentTopicData;
use App\Modules\ContentTopics\Data\UpdateContentTopicData;
use Illuminate\Database\Eloquent\Collection;

class EloquentContentTopicRepository implements ContentTopicRepository
{
    public function create(CreateContentTopicData $data): ContentTopic
    {
        return ContentTopic::query()->create([
            'category_id' => $data->categoryId,
            'title' => $data->title,
            'slug' => $data->slug,
            'cluster' => $data->cluster,
            'primary_keyword' => $data->primaryKeyword,
            'secondary_keywords' => $data->secondaryKeywords,
            'search_intent' => $data->searchIntent,
            'priority_score' => $data->priorityScore,
            'score_breakdown' => $data->scoreBreakdown,
            'discovery_metadata' => $data->discoveryMetadata,
            'difficulty_note' => $data->difficultyNote,
            'source' => $data->source,
            'status' => $data->status,
            'notes' => $data->notes,
            'approved_at' => $data->status === ContentTopic::STATUS_APPROVED ? now()->toDateTimeString() : null,
            'rejected_at' => $data->status === ContentTopic::STATUS_REJECTED ? now()->toDateTimeString() : null,
            'used_at' => $data->status === ContentTopic::STATUS_USED ? now()->toDateTimeString() : null,
        ]);
    }

    public function update(ContentTopic $topic, UpdateContentTopicData $data): ContentTopic
    {
        $topic->update([
            'category_id' => $data->categoryId,
            'title' => $data->title,
            'slug' => $data->slug,
            'cluster' => $data->cluster,
            'primary_keyword' => $data->primaryKeyword,
            'secondary_keywords' => $data->secondaryKeywords,
            'search_intent' => $data->searchIntent,
            'priority_score' => $data->priorityScore,
            'score_breakdown' => $data->scoreBreakdown,
            'discovery_metadata' => $data->discoveryMetadata,
            'difficulty_note' => $data->difficultyNote,
            'source' => $data->source,
            'notes' => $data->notes,
        ]);

        return $topic->refresh();
    }

    public function transition(ContentTopic $topic, ContentTopicStateTransitionData $data): ContentTopic
    {
        $topic->update([
            'status' => $data->status,
            'approved_at' => $data->approvedAt,
            'rejected_at' => $data->rejectedAt,
            'used_at' => $data->usedAt,
            'notes' => $data->notes ?? $topic->notes,
        ]);

        return $topic->refresh();
    }

    public function delete(ContentTopic $topic): void
    {
        $topic->delete();
    }

    public function findById(int $id): ?ContentTopic
    {
        return ContentTopic::query()->with('category')->find($id);
    }

    public function findBySlug(string $slug): ?ContentTopic
    {
        return ContentTopic::query()->with('category')->where('slug', $slug)->first();
    }

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool
    {
        return ContentTopic::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    public function existsDuplicate(string $title, int $categoryId, ?string $primaryKeyword = null, ?int $ignoreId = null): bool
    {
        $normalizedTitle = mb_strtolower(trim($title));
        $normalizedKeyword = $primaryKeyword !== null ? mb_strtolower(trim($primaryKeyword)) : null;

        return ContentTopic::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('category_id', $categoryId)
            ->where(function ($query) use ($normalizedTitle, $normalizedKeyword): void {
                $query->whereRaw('LOWER(title) = ?', [$normalizedTitle]);

                if ($normalizedKeyword !== null && $normalizedKeyword !== '') {
                    $query->orWhereRaw('LOWER(primary_keyword) = ?', [$normalizedKeyword]);
                }
            })
            ->exists();
    }

    /**
     * @return Collection<int, ContentTopic>
     */
    public function search(ContentTopicFiltersData $filters): Collection
    {
        [$sortColumn, $descending] = $this->normalizeSort($filters->sort);

        return ContentTopic::query()
            ->with('category')
            ->withExists(['draftGenerationJobs as has_draft_generation_job'])
            ->when($filters->search, function ($query, string $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('primary_keyword', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($filters->status, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters->recommendation, function ($query, string $recommendation): void {
                $query->where(function ($inner) use ($recommendation): void {
                    $inner->where('discovery_metadata->recommendation', $recommendation);

                    if ($recommendation === ContentTopic::RECOMMENDATION_DUPLICATE) {
                        $inner->orWhere('discovery_metadata->is_duplicate', true);

                        return;
                    }

                    if ($recommendation === ContentTopic::RECOMMENDATION_UNSCORED) {
                        $inner->orWhere(function ($fallback): void {
                            $fallback
                                ->whereNull('discovery_metadata->recommendation')
                                ->whereNull('priority_score')
                                ->where(function ($duplicateFallback): void {
                                    $duplicateFallback
                                        ->whereNull('discovery_metadata->is_duplicate')
                                        ->orWhere('discovery_metadata->is_duplicate', false);
                                });
                        });

                        return;
                    }

                    [$min, $max] = $this->recommendationScoreRange($recommendation);

                    if ($min === null && $max === null) {
                        return;
                    }

                    $inner->orWhere(function ($fallback) use ($min, $max): void {
                        $fallback
                            ->whereNull('discovery_metadata->recommendation')
                            ->whereNotNull('priority_score')
                            ->where(function ($duplicateFallback): void {
                                $duplicateFallback
                                    ->whereNull('discovery_metadata->is_duplicate')
                                    ->orWhere('discovery_metadata->is_duplicate', false);
                            });

                        if ($min !== null) {
                            $fallback->where('priority_score', '>=', $min);
                        }

                        if ($max !== null) {
                            $fallback->where('priority_score', '<=', $max);
                        }
                    });
                });
            })
            ->when($filters->categoryId, fn ($query, int $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters->cluster, fn ($query, string $cluster) => $query->where('cluster', $cluster))
            ->when($filters->source, fn ($query, string $source) => $query->where('source', $source))
            ->when($filters->isDuplicate !== null, function ($query) use ($filters): void {
                if ($filters->isDuplicate) {
                    $query->where('discovery_metadata->is_duplicate', true);

                    return;
                }

                $query->where(function ($inner): void {
                    $inner->whereNull('discovery_metadata->is_duplicate')
                        ->orWhere('discovery_metadata->is_duplicate', false);
                });
            })
            ->when($filters->hasDraftGenerationJob !== null, function ($query) use ($filters): void {
                if ($filters->hasDraftGenerationJob) {
                    $query->whereHas('draftGenerationJobs');

                    return;
                }

                $query->whereDoesntHave('draftGenerationJobs');
            })
            ->when($filters->priorityScoreMin !== null, fn ($query) => $query->where('priority_score', '>=', $filters->priorityScoreMin))
            ->when($filters->priorityScoreMax !== null, fn ($query) => $query->where('priority_score', '<=', $filters->priorityScoreMax))
            ->orderBy($sortColumn, $descending ? 'desc' : 'asc')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return array{0:string,1:bool}
     */
    private function normalizeSort(string $sort): array
    {
        $descending = str_starts_with($sort, '-');
        $field = ltrim($sort, '-');
        $allowed = ['created_at', 'updated_at', 'approved_at', 'used_at', 'priority_score', 'title'];

        if (! in_array($field, $allowed, true)) {
            return ['created_at', true];
        }

        return [$field, $descending];
    }

    /**
     * @return array{0:?float,1:?float}
     */
    private function recommendationScoreRange(string $recommendation): array
    {
        return match ($recommendation) {
            ContentTopic::RECOMMENDATION_AUTO_QUEUE => [85.0, null],
            ContentTopic::RECOMMENDATION_REVIEW => [70.0, 84.99],
            ContentTopic::RECOMMENDATION_LOW_SCORE => [50.0, 69.99],
            ContentTopic::RECOMMENDATION_DISCARDED => [0.0, 49.99],
            default => [null, null],
        };
    }
}
