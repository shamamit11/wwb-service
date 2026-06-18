<?php

namespace App\Modules\Ai\Repositories;

use App\Models\AiJob;
use App\Models\AiJobCost;
use App\Modules\Ai\Data\CreateAiJobCostData;
use Illuminate\Database\Eloquent\Collection;

interface AiJobCostRepository
{
    public function create(CreateAiJobCostData $data): AiJobCost;

    public function updateJobAggregate(AiJob $job, CreateAiJobCostData $data): AiJobCost;

    /**
     * @return Collection<int, AiJobCost>
     */
    public function findByJob(AiJob $job): Collection;
}
