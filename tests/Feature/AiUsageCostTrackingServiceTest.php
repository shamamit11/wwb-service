<?php

namespace Tests\Feature;

use App\Infrastructure\Ai\Data\AiUsageData;
use App\Models\AiGenerationStep;
use App\Models\AiJob;
use App\Modules\Ai\Services\RecordAiUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiUsageCostTrackingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('ai.service.pricing.default_currency', 'USD');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.input_per_1k_tokens', '0.001');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.output_per_1k_tokens', '0.003');
    }

    public function test_usage_service_records_step_usage_and_refreshes_job_aggregate(): void
    {
        $service = app(RecordAiUsageService::class);

        $job = AiJob::query()->create([
            'type' => 'blog_writer',
            'status' => AiJob::STATUS_PROCESSING,
            'provider' => 'openai',
            'model' => 'gpt-5-mini',
            'attempts' => 1,
        ]);

        $step = AiGenerationStep::query()->create([
            'ai_job_id' => $job->id,
            'agent_name' => 'BlogWriterAgent',
            'status' => AiGenerationStep::STATUS_PROCESSING,
        ]);

        $cost = $service->recordForStep(
            $job,
            $step,
            new AiUsageData(
                promptTokens: 100,
                completionTokens: 50,
                cacheWriteInputTokens: 10,
                cacheReadInputTokens: 5,
                reasoningTokens: 20,
            ),
            actualCost: '0.12000000',
            metadata: ['call_id' => 'abc123'],
        );

        $aggregate = $job->fresh()->costs()->whereNull('ai_generation_step_id')->firstOrFail();

        $this->assertSame(115, $cost->input_tokens);
        $this->assertSame(70, $cost->output_tokens);
        $this->assertSame(185, $cost->total_tokens);
        $this->assertSame('0.00032500', $cost->estimated_cost);
        $this->assertSame('0.12000000', $cost->actual_cost);
        $this->assertSame('USD', $cost->currency);
        $this->assertSame('abc123', $cost->metadata['call_id']);

        $this->assertSame(115, $aggregate->input_tokens);
        $this->assertSame(70, $aggregate->output_tokens);
        $this->assertSame(185, $aggregate->total_tokens);
        $this->assertSame('0.00032500', $aggregate->estimated_cost);
        $this->assertSame('0.12000000', $aggregate->actual_cost);
        $this->assertSame('job_aggregate', $aggregate->metadata['scope']);

        $this->assertDatabaseCount('ai_job_costs', 2);
    }

    public function test_usage_service_tolerates_missing_usage_metadata_without_failing(): void
    {
        $service = app(RecordAiUsageService::class);

        $job = AiJob::query()->create([
            'type' => 'topic_discovery',
            'status' => AiJob::STATUS_PROCESSING,
            'provider' => 'openai',
            'model' => 'gpt-5-mini',
            'attempts' => 1,
        ]);

        $step = AiGenerationStep::query()->create([
            'ai_job_id' => $job->id,
            'agent_name' => 'TopicDiscoveryAgent',
            'status' => AiGenerationStep::STATUS_PROCESSING,
        ]);

        $cost = $service->recordForStep($job, $step, null);

        $this->assertSame(0, $cost->input_tokens);
        $this->assertSame(0, $cost->output_tokens);
        $this->assertSame(0, $cost->total_tokens);
        $this->assertSame('0.00000000', $cost->estimated_cost);
        $this->assertNull($cost->actual_cost);

        $this->assertDatabaseHas('ai_job_costs', [
            'id' => $cost->id,
            'ai_job_id' => $job->id,
            'ai_generation_step_id' => $step->id,
            'input_tokens' => 0,
            'output_tokens' => 0,
            'total_tokens' => 0,
        ]);
    }
}
