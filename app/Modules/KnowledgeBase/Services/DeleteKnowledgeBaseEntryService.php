<?php

namespace App\Modules\KnowledgeBase\Services;

use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;

class DeleteKnowledgeBaseEntryService
{
    public function __construct(
        private readonly KnowledgeBaseEntryRepository $entries,
    ) {}

    public function handle(KnowledgeBaseEntry $entry): void
    {
        $this->entries->delete($entry);
    }
}
