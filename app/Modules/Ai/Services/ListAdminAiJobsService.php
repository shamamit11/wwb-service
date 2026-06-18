<?php

namespace App\Modules\Ai\Services;

use App\Modules\Ai\Data\AiJobFiltersData;
use App\Modules\Ai\Repositories\AiJobRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminAiJobsService
{
    public function __construct(
        private readonly AiJobRepository $jobs,
    ) {}

    public function handle(AiJobFiltersData $filters): Collection
    {
        return $this->jobs->search($filters);
    }
}
