<?php

namespace App\Modules\ContentBriefs\Repositories;

use App\Models\ContentBrief;
use App\Modules\ContentBriefs\Data\ContentBriefFiltersData;
use App\Modules\ContentBriefs\Data\CreateContentBriefData;
use App\Modules\ContentBriefs\Data\UpdateContentBriefData;
use Illuminate\Database\Eloquent\Collection;

interface ContentBriefRepository
{
    public function create(CreateContentBriefData $data): ContentBrief;

    public function update(ContentBrief $brief, UpdateContentBriefData $data): ContentBrief;

    public function findById(int $id): ?ContentBrief;

    public function findByTopicId(int $contentTopicId): ?ContentBrief;

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool;

    /**
     * @return Collection<int, ContentBrief>
     */
    public function search(ContentBriefFiltersData $filters): Collection;
}
