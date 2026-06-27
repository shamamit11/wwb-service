<?php

namespace App\Modules\KnowledgeBase\Repositories;

use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\CreateKnowledgeBaseEntryData;
use App\Modules\KnowledgeBase\Data\KnowledgeBaseEntryFiltersData;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use App\Modules\KnowledgeBase\Data\UpdateKnowledgeBaseEntryData;
use Illuminate\Database\Eloquent\Collection;

class EloquentKnowledgeBaseEntryRepository implements KnowledgeBaseEntryRepository
{
    public function create(CreateKnowledgeBaseEntryData $data): KnowledgeBaseEntry
    {
        return KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $data->createdByUserId,
            'updated_by_user_id' => $data->updatedByUserId,
            'title' => $data->title,
            'slug' => $data->slug,
            'entry_type' => $data->entryType,
            'status' => $data->status,
            'summary' => $data->summary,
            'content_markdown' => $data->contentMarkdown,
            'source_url' => $data->sourceUrl,
            'featured_media_id' => $data->featuredMediaId,
            'metadata' => $data->metadata,
        ])->load($this->relations());
    }

    public function update(KnowledgeBaseEntry $entry, UpdateKnowledgeBaseEntryData $data): KnowledgeBaseEntry
    {
        $entry->update([
            'updated_by_user_id' => $data->updatedByUserId,
            'title' => $data->title,
            'slug' => $data->slug,
            'entry_type' => $data->entryType,
            'status' => $data->status,
            'summary' => $data->summary,
            'content_markdown' => $data->contentMarkdown,
            'source_url' => $data->sourceUrl,
            'featured_media_id' => $data->featuredMediaId,
            'metadata' => $data->metadata,
        ]);

        return $entry->refresh()->load($this->relations());
    }

    public function updateMetadata(KnowledgeBaseEntry $entry, ?array $metadata): KnowledgeBaseEntry
    {
        $entry->update([
            'metadata' => $metadata,
        ]);

        return $entry->refresh()->load($this->relations());
    }

    public function delete(KnowledgeBaseEntry $entry): void
    {
        $entry->delete();
    }

    public function findById(int $id): ?KnowledgeBaseEntry
    {
        return KnowledgeBaseEntry::query()
            ->with($this->relations())
            ->find($id);
    }

    public function findBySlug(string $slug): ?KnowledgeBaseEntry
    {
        return KnowledgeBaseEntry::query()
            ->with($this->relations())
            ->where('slug', $slug)
            ->first();
    }

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool
    {
        return KnowledgeBaseEntry::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    /**
     * @return Collection<int, KnowledgeBaseEntry>
     */
    public function getAllOrdered(): Collection
    {
        return $this->searchAdmin(new KnowledgeBaseEntryFiltersData);
    }

    /**
     * @return Collection<int, KnowledgeBaseEntry>
     */
    public function searchAdmin(KnowledgeBaseEntryFiltersData $filters): Collection
    {
        [$sortColumn, $descending] = $this->normalizeSort($filters->sort);

        return KnowledgeBaseEntry::query()
            ->with($this->relations())
            ->when($filters->search, function ($query, string $search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhere('content_markdown', 'like', "%{$search}%")
                        ->orWhere('source_url', 'like', "%{$search}%");
                });
            })
            ->when($filters->status, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters->entryType, fn ($query, string $entryType) => $query->where('entry_type', $entryType))
            ->when($filters->createdByUserId, fn ($query, int $createdByUserId) => $query->where('created_by_user_id', $createdByUserId))
            ->when($filters->featuredMediaId, fn ($query, int $featuredMediaId) => $query->where('featured_media_id', $featuredMediaId))
            ->orderBy($sortColumn, $descending ? 'desc' : 'asc')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, KnowledgeBaseEntry>
     */
    public function findActiveForContext(KnowledgeContextQueryData $query): Collection
    {
        return KnowledgeBaseEntry::query()
            ->with($this->relations())
            ->where('status', KnowledgeBaseEntry::STATUS_ACTIVE)
            ->when(
                $query->entryTypes !== [],
                fn ($builder) => $builder->whereIn('entry_type', $this->normalizeEntryTypes($query->entryTypes))
            )
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(max(1, min(100, $query->candidatePoolSize)))
            ->get();
    }

    /**
     * @return list<string>
     */
    private function relations(): array
    {
        return ['createdBy', 'updatedBy', 'featuredMedia'];
    }

    /**
     * @return array{0:string,1:bool}
     */
    private function normalizeSort(string $sort): array
    {
        $descending = str_starts_with($sort, '-');
        $field = ltrim($sort, '-');
        $allowed = ['title', 'created_at', 'updated_at'];

        if (! in_array($field, $allowed, true)) {
            return ['updated_at', true];
        }

        return [$field, $descending];
    }

    /**
     * @param  list<string>  $entryTypes
     * @return list<string>
     */
    private function normalizeEntryTypes(array $entryTypes): array
    {
        return array_values(array_intersect(
            KnowledgeBaseEntry::ENTRY_TYPES,
            array_values(array_filter($entryTypes, static fn (mixed $entryType): bool => is_string($entryType) && $entryType !== '')),
        ));
    }
}
