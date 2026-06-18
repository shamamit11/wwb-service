<?php

namespace App\Modules\ContentBriefs\Services;

use App\Modules\ContentBriefs\Data\ContentBriefFiltersData;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminContentBriefsService
{
    public function __construct(
        private readonly ContentBriefRepository $briefs,
    ) {}

    public function handle(ContentBriefFiltersData $filters): Collection
    {
        return $this->briefs->search($filters);
    }
}
