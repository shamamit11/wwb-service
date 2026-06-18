<?php

namespace App\Modules\Ai\Repositories;

use App\Models\AiGenerationStep;
use App\Modules\Ai\Data\CreateAiGenerationStepData;
use App\Modules\Ai\Data\UpdateAiGenerationStepStatusData;

interface AiGenerationStepRepository
{
    public function create(CreateAiGenerationStepData $data): AiGenerationStep;

    public function updateStatus(AiGenerationStep $step, UpdateAiGenerationStepStatusData $data): AiGenerationStep;
}
