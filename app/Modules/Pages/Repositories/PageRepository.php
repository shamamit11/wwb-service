<?php

namespace App\Modules\Pages\Repositories;

use App\Models\Page;
use App\Modules\Pages\Data\CreatePageData;
use App\Modules\Pages\Data\PageFiltersData;
use App\Modules\Pages\Data\UpdatePageData;
use Illuminate\Database\Eloquent\Collection;

interface PageRepository
{
    public function create(CreatePageData $data): Page;

    public function update(Page $page, UpdatePageData $data): Page;

    public function delete(Page $page): void;

    public function findById(int $id): ?Page;

    public function findBySlug(string $slug): ?Page;

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool;

    /**
     * @return Collection<int, Page>
     */
    public function searchAdmin(PageFiltersData $filters): Collection;
}
