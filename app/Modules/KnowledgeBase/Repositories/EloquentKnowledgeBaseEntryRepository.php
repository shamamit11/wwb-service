<?php

namespace App\Modules\KnowledgeBase\Repositories;

use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\CreateKnowledgeBaseEntryData;
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
        return KnowledgeBaseEntry::query()
            ->with($this->relations())
            ->orderBy('title')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return list<string>
     */
    private function relations(): array
    {
        return ['createdBy', 'updatedBy', 'featuredMedia'];
    }
}
