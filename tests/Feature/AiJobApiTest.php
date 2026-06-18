<?php

namespace Tests\Feature;

use App\Jobs\AI\DiscoverContentTopicsJob;
use App\Models\AiGenerationStep;
use App\Models\AiJob;
use App\Models\AiJobCost;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AiJobApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ai_job_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/ai-jobs')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');

        $this->postJson('/api/v1/admin/ai-jobs/topic-discovery', [
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
        ])->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_queue_topic_discovery_job(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/admin/ai-jobs/topic-discovery', [
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'count' => 4,
            'audience' => 'Editorial leads',
            'prompt_template_key' => 'topic_discovery_default',
            'metadata' => [
                'knowledge_context_filters' => [
                    'clusters' => ['ai_tools'],
                ],
            ],
        ]);

        $response->assertAccepted()
            ->assertJsonPath('data.type', 'topic_discovery')
            ->assertJsonPath('data.status', AiJob::STATUS_QUEUED)
            ->assertJsonPath('data.entity_type', 'content_topic_batch')
            ->assertJsonPath('data.entity_id', null)
            ->assertJsonPath('data.input_payload.cluster', ContentTopic::CLUSTER_AI_TOOLS)
            ->assertJsonPath('data.input_payload.count', 4)
            ->assertJsonPath('data.input_payload.audience', 'Editorial leads')
            ->assertJsonPath('data.input_payload.prompt_template_key', 'topic_discovery_default')
            ->assertJsonPath('data.input_payload.metadata.trigger', 'admin_api')
            ->assertJsonPath('data.can_retry', false);

        $this->assertDatabaseHas('ai_jobs', [
            'type' => 'topic_discovery',
            'status' => AiJob::STATUS_QUEUED,
            'entity_type' => 'content_topic_batch',
            'attempts' => 1,
        ]);

        Queue::assertPushed(DiscoverContentTopicsJob::class, 1);
    }

    public function test_topic_discovery_endpoint_validates_payload(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/admin/ai-jobs/topic-discovery', [
            'cluster' => 'unknown_cluster',
            'count' => 0,
            'prompt_template_key' => str_repeat('a', 191),
            'metadata' => 'invalid',
        ])
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => [
                    'cluster',
                    'count',
                    'prompt_template_key',
                    'metadata',
                ],
            ]);
    }

    public function test_admin_can_list_show_and_retry_failed_ai_jobs(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'Retry Topic',
            'slug' => 'retry-topic',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'retry topic',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '80.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => null,
            'approved_at' => now(),
        ]);

        $brief = ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'Retry Brief',
            'slug' => 'retry-brief',
            'meta_title' => null,
            'meta_description' => null,
            'primary_keyword' => 'retry topic',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'outline' => [['heading' => 'Intro', 'purpose' => 'Frame it']],
            'headings' => ['Intro'],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $completed = AiJob::query()->create([
            'type' => 'content_brief',
            'status' => AiJob::STATUS_COMPLETED,
            'entity_type' => 'content_topic',
            'entity_id' => 24,
            'provider' => 'openai',
            'model' => 'gpt-5-mini',
            'input_payload' => ['topic' => 'AI memory'],
            'output_payload' => ['title' => 'Memory systems'],
            'usage_payload' => ['prompt_tokens' => 100],
            'attempts' => 1,
            'completed_at' => now(),
        ]);

        $failed = AiJob::query()->create([
            'type' => 'blog_writer',
            'status' => AiJob::STATUS_FAILED,
            'entity_type' => 'content_brief',
            'entity_id' => $brief->id,
            'provider' => 'openai',
            'model' => 'gpt-5-mini',
            'input_payload' => ['content_brief_id' => $brief->id, 'category_id' => 7],
            'output_payload' => ['draft' => null],
            'usage_payload' => ['prompt_tokens' => 180],
            'error_message' => 'Provider timeout.',
            'attempts' => 1,
            'started_at' => now()->subMinute(),
            'failed_at' => now(),
        ]);

        AiGenerationStep::query()->create([
            'ai_job_id' => $failed->id,
            'agent_name' => 'BlogWriterAgent',
            'status' => AiGenerationStep::STATUS_FAILED,
            'input_payload' => ['section_count' => 6],
            'output_payload' => ['raw' => 'partial text'],
            'usage_payload' => ['completion_tokens' => 55],
            'error_message' => 'Malformed provider response.',
            'started_at' => now()->subMinute(),
            'failed_at' => now(),
        ]);

        AiJobCost::query()->create([
            'ai_job_id' => $failed->id,
            'ai_generation_step_id' => null,
            'provider' => 'openai',
            'model' => 'gpt-5-mini',
            'input_tokens' => 180,
            'output_tokens' => 55,
            'total_tokens' => 235,
            'estimated_cost' => '0.00420000',
            'actual_cost' => null,
            'currency' => 'USD',
            'metadata' => ['scope' => 'job_aggregate'],
        ]);

        $this->withToken($token)->getJson('/api/v1/admin/ai-jobs?status=failed&provider=openai&model=gpt-5-mini')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $failed->id)
            ->assertJsonPath('data.0.steps_count', 1)
            ->assertJsonPath('data.0.can_retry', true)
            ->assertJsonPath('data.0.cost_summary.total_tokens', 235)
            ->assertJsonPath('data.0.cost_summary.currency', 'USD');

        $this->withToken($token)->getJson("/api/v1/admin/ai-jobs/{$failed->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $failed->id)
            ->assertJsonPath('data.error_message', 'Provider timeout.')
            ->assertJsonPath('data.steps.0.agent_name', 'BlogWriterAgent')
            ->assertJsonPath('data.steps.0.status', AiGenerationStep::STATUS_FAILED)
            ->assertJsonPath('data.costs.0.provider', 'openai')
            ->assertJsonPath('data.costs.0.model', 'gpt-5-mini')
            ->assertJsonPath('data.cost_summary.estimated_cost', '0.00420000');

        $retryResponse = $this->withToken($token)->postJson("/api/v1/admin/ai-jobs/{$failed->id}/retry");

        $retryResponse->assertAccepted()
            ->assertJsonPath('data.status', AiJob::STATUS_QUEUED)
            ->assertJsonPath('data.retry_of_ai_job_id', $failed->id)
            ->assertJsonPath('data.attempts', 2)
            ->assertJsonPath('data.type', 'blog_writer')
            ->assertJsonPath('data.entity_type', 'content_brief')
            ->assertJsonPath('data.entity_id', $brief->id)
            ->assertJsonPath('data.can_retry', false);

        $this->assertDatabaseCount('ai_jobs', 3);
        $this->assertDatabaseHas('ai_jobs', [
            'retry_of_ai_job_id' => $failed->id,
            'status' => AiJob::STATUS_QUEUED,
            'attempts' => 2,
        ]);

        $this->assertDatabaseHas('ai_jobs', [
            'id' => $completed->id,
            'status' => AiJob::STATUS_COMPLETED,
        ]);

        Queue::assertPushed(\App\Jobs\AI\GenerateBlogDraftJob::class, 1);
    }

    public function test_retry_endpoint_rejects_non_failed_jobs(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $job = AiJob::query()->create([
            'type' => 'topic_discovery',
            'status' => AiJob::STATUS_COMPLETED,
            'attempts' => 1,
            'completed_at' => now(),
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/ai-jobs/{$job->id}/retry")
            ->assertStatus(409)
            ->assertJsonPath('error_code', 'CONFLICT')
            ->assertJsonPath('errors.status.0', AiJob::STATUS_COMPLETED);

        $this->assertDatabaseCount('ai_jobs', 1);
    }
}
