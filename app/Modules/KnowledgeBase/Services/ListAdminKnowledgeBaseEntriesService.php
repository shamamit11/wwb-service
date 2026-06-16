<?php

namespace App\Modules\KnowledgeBase\Services;

use App\Modules\KnowledgeBase\Data\KnowledgeBaseEntryFiltersData;
use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminKnowledgeBaseEntriesService
{
    public function __construct(
        private readonly KnowledgeBaseEntryRepository $entries,
    ) {}

    public function handle(?KnowledgeBaseEntryFiltersData $filters = null): Collection
    {
        return $this->entries->searchAdmin($filters ?? new KnowledgeBaseEntryFiltersData);
    }
}
