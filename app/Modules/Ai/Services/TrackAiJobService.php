<?php

namespace App\Modules\Ai\Services;

use App\Models\AiGenerationStep;
use App\Models\AiJob;
use App\Modules\Ai\Data\CreateAiGenerationStepData;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Data\UpdateAiGenerationStepStatusData;
use App\Modules\Ai\Data\UpdateAiJobStatusData;
use App\Modules\Ai\Repositories\AiGenerationStepRepository;
use App\Modules\Ai\Repositories\AiJobRepository;

class TrackAiJobService
{
    public function __construct(
        private readonly AiJobRepository $jobs,
        private readonly AiGenerationStepRepository $steps,
    ) {}

    public function createJob(CreateAiJobData $data): AiJob
    {
        return $this->jobs->create($data);
    }

    public function queueJob(AiJob $job): AiJob
    {
        return $this->jobs->updateStatus($job, new UpdateAiJobStatusData(
            status: AiJob::STATUS_QUEUED,
        ));
    }

    public function startJob(AiJob $job): AiJob
    {
        return $this->jobs->updateStatus($job, new UpdateAiJobStatusData(
            status: AiJob::STATUS_PROCESSING,
            startedAt: now()->toISOString(),
            completedAt: null,
            failedAt: null,
        ));
    }

    /**
     * @param  array<string, mixed>|null  $outputPayload
     * @param  array<string, mixed>|null  $usagePayload
     */
    public function completeJob(AiJob $job, ?array $outputPayload = null, ?array $usagePayload = null): AiJob
    {
        return $this->jobs->updateStatus($job, new UpdateAiJobStatusData(
            status: AiJob::STATUS_COMPLETED,
            outputPayload: $outputPayload,
            usagePayload: $usagePayload,
            errorMessage: null,
            completedAt: now()->toISOString(),
            failedAt: null,
        ));
    }

    /**
     * @param  array<string, mixed>|null  $outputPayload
     * @param  array<string, mixed>|null  $usagePayload
     */
    public function failJob(
        AiJob $job,
        string $errorMessage,
        ?array $outputPayload = null,
        ?array $usagePayload = null,
    ): AiJob {
        return $this->jobs->updateStatus($job, new UpdateAiJobStatusData(
            status: AiJob::STATUS_FAILED,
            outputPayload: $outputPayload,
            usagePayload: $usagePayload,
            errorMessage: $errorMessage,
            failedAt: now()->toISOString(),
            completedAt: null,
        ));
    }

    public function cancelJob(AiJob $job, ?string $errorMessage = null): AiJob
    {
        return $this->jobs->updateStatus($job, new UpdateAiJobStatusData(
            status: AiJob::STATUS_CANCELLED,
            errorMessage: $errorMessage,
        ));
    }

    public function reviewJob(AiJob $job): AiJob
    {
        return $this->jobs->updateStatus($job, new UpdateAiJobStatusData(
            status: AiJob::STATUS_REVIEWED,
        ));
    }

    public function createStep(CreateAiGenerationStepData $data): AiGenerationStep
    {
        return $this->steps->create($data);
    }

    public function startStep(AiGenerationStep $step): AiGenerationStep
    {
        return $this->steps->updateStatus($step, new UpdateAiGenerationStepStatusData(
            status: AiGenerationStep::STATUS_PROCESSING,
            startedAt: now()->toISOString(),
            completedAt: null,
            failedAt: null,
        ));
    }

    /**
     * @param  array<string, mixed>|null  $outputPayload
     * @param  array<string, mixed>|null  $usagePayload
     */
    public function completeStep(
        AiGenerationStep $step,
        ?array $outputPayload = null,
        ?array $usagePayload = null,
    ): AiGenerationStep {
        return $this->steps->updateStatus($step, new UpdateAiGenerationStepStatusData(
            status: AiGenerationStep::STATUS_COMPLETED,
            outputPayload: $outputPayload,
            usagePayload: $usagePayload,
            errorMessage: null,
            completedAt: now()->toISOString(),
            failedAt: null,
        ));
    }

    /**
     * @param  array<string, mixed>|null  $outputPayload
     * @param  array<string, mixed>|null  $usagePayload
     */
    public function failStep(
        AiGenerationStep $step,
        string $errorMessage,
        ?array $outputPayload = null,
        ?array $usagePayload = null,
    ): AiGenerationStep {
        return $this->steps->updateStatus($step, new UpdateAiGenerationStepStatusData(
            status: AiGenerationStep::STATUS_FAILED,
            outputPayload: $outputPayload,
            usagePayload: $usagePayload,
            errorMessage: $errorMessage,
            failedAt: now()->toISOString(),
            completedAt: null,
        ));
    }
}
