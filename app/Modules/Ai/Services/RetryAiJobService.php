<?php

namespace App\Modules\Ai\Services;

use App\Models\AiJob;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Exceptions\AiJobRetryNotAllowedException;
use App\Modules\Ai\Repositories\AiJobRepository;

class RetryAiJobService
{
    public function __construct(
        private readonly AiJobRepository $jobs,
    ) {}

    public function handle(AiJob $job): AiJob
    {
        $job = $this->jobs->findById((int) $job->id) ?? $job;

        if (! $job->canRetry()) {
            throw new AiJobRetryNotAllowedException($job->status);
        }

        return $this->jobs->create(new CreateAiJobData(
            type: $job->type,
            status: AiJob::STATUS_QUEUED,
            entityType: $job->entity_type,
            entityId: $job->entity_id,
            provider: $job->provider,
            model: $job->model,
            inputPayload: $job->input_payload,
            attempts: $job->attempts + 1,
            retryOfAiJobId: (int) $job->id,
        ));
    }
}
