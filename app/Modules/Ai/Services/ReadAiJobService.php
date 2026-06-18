<?php

namespace App\Modules\Ai\Services;

use App\Models\AiJob;
use App\Modules\Ai\Repositories\AiJobRepository;

class ReadAiJobService
{
    public function __construct(
        private readonly AiJobRepository $jobs,
    ) {}

    public function handle(int $id): AiJob
    {
        return $this->jobs->findById($id) ?? throw (new AiJob)->newModelQuery()->whereKey($id)->firstOrFail();
    }
}
