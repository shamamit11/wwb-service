<?php

namespace App\Modules\Ai\Repositories;

use App\Models\AiJob;
use App\Modules\Ai\Data\AiJobFiltersData;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Data\UpdateAiJobStatusData;
use Illuminate\Database\Eloquent\Collection;

interface AiJobRepository
{
    public function create(CreateAiJobData $data): AiJob;

    public function updateStatus(AiJob $job, UpdateAiJobStatusData $data): AiJob;

    public function findById(int $id): ?AiJob;

    /**
     * @return Collection<int, AiJob>
     */
    public function search(AiJobFiltersData $filters): Collection;
}
