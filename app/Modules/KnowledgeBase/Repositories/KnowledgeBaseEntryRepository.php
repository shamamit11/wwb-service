<?php

namespace App\Modules\KnowledgeBase\Repositories;

use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\CreateKnowledgeBaseEntryData;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use App\Modules\KnowledgeBase\Data\KnowledgeBaseEntryFiltersData;
use App\Modules\KnowledgeBase\Data\UpdateKnowledgeBaseEntryData;
use Illuminate\Database\Eloquent\Collection;

interface KnowledgeBaseEntryRepository
{
    public function create(CreateKnowledgeBaseEntryData $data): KnowledgeBaseEntry;

    public function update(KnowledgeBaseEntry $entry, UpdateKnowledgeBaseEntryData $data): KnowledgeBaseEntry;

    public function updateMetadata(KnowledgeBaseEntry $entry, ?array $metadata): KnowledgeBaseEntry;

    public function delete(KnowledgeBaseEntry $entry): void;

    public function findById(int $id): ?KnowledgeBaseEntry;

    public function findBySlug(string $slug): ?KnowledgeBaseEntry;

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool;

    /**
     * @return Collection<int, KnowledgeBaseEntry>
     */
    public function getAllOrdered(): Collection;

    /**
     * @return Collection<int, KnowledgeBaseEntry>
     */
    public function searchAdmin(KnowledgeBaseEntryFiltersData $filters): Collection;

    /**
     * @return Collection<int, KnowledgeBaseEntry>
     */
    public function findActiveForContext(KnowledgeContextQueryData $query): Collection;
}
