<?php

namespace Tests\Feature;

use App\Models\AiGenerationStep;
use App\Models\AiJob;
use App\Modules\Ai\Data\CreateAiGenerationStepData;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Services\TrackAiJobService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiJobTrackingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_service_can_create_and_transition_jobs_and_steps(): void
    {
        $service = app(TrackAiJobService::class);

        $job = $service->createJob(new CreateAiJobData(
            type: 'topic_discovery',
            entityType: 'knowledge_base_run',
            entityId: 7,
            provider: 'openai',
            model: 'gpt-5-mini',
            inputPayload: ['clusters' => ['ai_tools']],
        ));

        $queuedJob = $service->queueJob($job);
        $processingJob = $service->startJob($queuedJob);

        $step = $service->createStep(new CreateAiGenerationStepData(
            aiJobId: $processingJob->id,
            agentName: 'TopicDiscoveryAgent',
            inputPayload: ['clusters' => ['ai_tools']],
        ));

        $processingStep = $service->startStep($step);
        $completedStep = $service->completeStep(
            $processingStep,
            outputPayload: ['topics' => [['title' => 'AI Topic']]],
            usagePayload: ['prompt_tokens' => 90, 'completion_tokens' => 30],
        );

        $completedJob = $service->completeJob(
            $processingJob,
            outputPayload: ['topics_saved' => 1],
            usagePayload: ['prompt_tokens' => 90, 'completion_tokens' => 30],
        );

        $this->assertSame(AiJob::STATUS_COMPLETED, $completedJob->status);
        $this->assertNotNull($completedJob->started_at);
        $this->assertNotNull($completedJob->completed_at);
        $this->assertSame(['topics_saved' => 1], $completedJob->output_payload);

        $this->assertSame(AiGenerationStep::STATUS_COMPLETED, $completedStep->status);
        $this->assertNotNull($completedStep->started_at);
        $this->assertNotNull($completedStep->completed_at);
        $this->assertSame(30, $completedStep->usage_payload['completion_tokens']);

        $this->assertDatabaseHas('ai_jobs', [
            'id' => $completedJob->id,
            'type' => 'topic_discovery',
            'status' => AiJob::STATUS_COMPLETED,
            'entity_type' => 'knowledge_base_run',
            'entity_id' => 7,
        ]);

        $this->assertDatabaseHas('ai_generation_steps', [
            'id' => $completedStep->id,
            'ai_job_id' => $completedJob->id,
            'agent_name' => 'TopicDiscoveryAgent',
            'status' => AiGenerationStep::STATUS_COMPLETED,
        ]);
    }
}
