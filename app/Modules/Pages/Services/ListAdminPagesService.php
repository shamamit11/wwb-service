<?php

namespace App\Modules\Pages\Services;

use App\Modules\Pages\Data\PageFiltersData;
use App\Modules\Pages\Repositories\PageRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminPagesService
{
    public function __construct(
        private readonly PageRepository $pages,
    ) {}

    public function handle(?PageFiltersData $filters = null): Collection
    {
        return $this->pages->searchAdmin($filters ?? new PageFiltersData);
    }
}
