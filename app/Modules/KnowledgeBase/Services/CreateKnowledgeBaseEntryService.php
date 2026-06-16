<?php

namespace App\Modules\KnowledgeBase\Services;

use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\CreateKnowledgeBaseEntryData;
use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;

class CreateKnowledgeBaseEntryService
{
    public function __construct(
        private readonly KnowledgeBaseEntryRepository $entries,
        private readonly KnowledgeBaseEntrySlugResolver $slugResolver,
    ) {}

    public function handle(CreateKnowledgeBaseEntryData $data): KnowledgeBaseEntry
    {
        return $this->entries->create(new CreateKnowledgeBaseEntryData(
            createdByUserId: $data->createdByUserId,
            updatedByUserId: $data->updatedByUserId,
            title: $data->title,
            slug: $this->slugResolver->resolve($data->title, $data->slug),
            entryType: $data->entryType,
            status: $data->status,
            summary: $data->summary,
            contentMarkdown: $data->contentMarkdown,
            sourceUrl: $data->sourceUrl,
            featuredMediaId: $data->featuredMediaId,
            metadata: $data->metadata,
        ));
    }
}
