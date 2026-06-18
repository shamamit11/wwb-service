<?php

namespace Tests\Feature;

use App\Jobs\AI\DiscoverContentTopicsJob;
use App\Modules\Ai\Services\RunTopicDiscoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Tests\TestCase;

class TopicDiscoveryExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_dispatches_topic_discovery_job_to_ai_queue(): void
    {
        Bus::fake();

        $this->artisan('ai:discover-topics', [
            '--cluster' => 'ai_tools',
            '--count' => 10,
        ])->assertSuccessful();

        Bus::assertDispatched(DiscoverContentTopicsJob::class, function (DiscoverContentTopicsJob $job): bool {
            return $job->cluster === 'ai_tools'
                && $job->count === 10
                && $job->queue === 'ai';
        });
    }

    public function test_command_can_run_topic_discovery_synchronously(): void
    {
        $service = Mockery::mock(RunTopicDiscoveryService::class);
        $service->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(fn ($data): bool => $data->cluster === 'seo' && $data->count === 3))
            ->andReturn(new \App\AI\DTO\AgentResult(
                agent: 'TopicDiscoveryAgent',
                status: \App\AI\Enums\AiRunStatus::SUCCESS,
                metadata: [
                    'saved_topic_ids' => [11, 12],
                    'skipped_duplicates' => [['title' => 'Duplicate']],
                ],
            ));

        $this->app->instance(RunTopicDiscoveryService::class, $service);

        $this->artisan('ai:discover-topics', [
            '--cluster' => 'seo',
            '--count' => 3,
            '--sync' => true,
        ])
            ->expectsOutputToContain('Topic discovery completed for [seo].')
            ->expectsOutputToContain('Saved topics: 2')
            ->expectsOutputToContain('Skipped duplicates: 1')
            ->assertSuccessful();
    }

    public function test_discover_content_topics_job_is_retry_safe_for_topic_creation(): void
    {
        $this->seedTopicDiscoveryPromptTemplate();

        $author = \App\Models\User::factory()->create();

        \App\Models\KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'title' => 'Prompt Hygiene',
            'slug' => 'prompt-hygiene',
            'entry_type' => \App\Models\KnowledgeBaseEntry::TYPE_REFERENCE,
            'status' => \App\Models\KnowledgeBaseEntry::STATUS_ACTIVE,
            'summary' => 'Keep prompts explicit and scoped.',
            'content_markdown' => 'Prompt guidance',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => null,
        ]);

        $fakeClient = new class implements \App\Infrastructure\Ai\Contracts\AiClient
        {
            public int $calls = 0;

            public function generateText(\App\Infrastructure\Ai\Data\GenerateTextRequest $request): \App\Infrastructure\Ai\Data\TextGenerationResult
            {
                $this->calls++;

                return new \App\Infrastructure\Ai\Data\TextGenerationResult(
                    content: json_encode([
                        'topics' => [
                            [
                                'title' => 'AI Tool Playbooks for Editorial Teams',
                                'primary_keyword' => 'ai tool playbooks',
                                'secondary_keywords' => ['editorial systems'],
                                'search_intent' => 'informational',
                                'priority_score' => 92,
                                'difficulty_note' => 'Specific operational angle.',
                                'summary' => 'Operational guidance topic.',
                            ],
                        ],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'openai',
                    model: 'gpt-5-mini',
                    usage: new \App\Infrastructure\Ai\Data\AiUsageData(promptTokens: 10, completionTokens: 5),
                );
            }
        };

        $this->app->instance(\App\Infrastructure\Ai\Contracts\AiClient::class, $fakeClient);

        $job = new DiscoverContentTopicsJob(cluster: 'ai_tools', count: 1);
        $job->handle(app(RunTopicDiscoveryService::class));
        $job->handle(app(RunTopicDiscoveryService::class));

        $this->assertSame(2, $fakeClient->calls);
        $this->assertDatabaseCount('content_topics', 1);
        $this->assertDatabaseHas('content_topics', [
            'title' => 'AI Tool Playbooks for Editorial Teams',
            'cluster' => 'ai_tools',
            'status' => \App\Models\ContentTopic::STATUS_SUGGESTED,
            'source' => \App\Models\ContentTopic::SOURCE_AI_SUGGESTED,
        ]);
    }

    public function test_schedule_list_includes_topic_discovery_tasks(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('ai:discover-topics --cluster=ai_tools --count=10')
            ->expectsOutputToContain('ai:discover-topics --cluster=developer_ai --count=10')
            ->assertSuccessful();
    }

    private function seedTopicDiscoveryPromptTemplate(): void
    {
        $template = \App\Models\AiPromptTemplate::query()->create([
            'name' => 'Topic Discovery Default',
            'key' => 'topic_discovery_default',
            'type' => \App\Models\AiPromptTemplate::TYPE_TOPIC_DISCOVERY,
            'description' => 'Default discovery prompt.',
            'status' => \App\Models\AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $version = \App\Models\AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $template->id,
            'version' => 1,
            'system_prompt' => 'Discover {{target_count}} topics for {{cluster}}.',
            'user_prompt' => 'Audience {{audience}} Existing {{existing_topics}} Knowledge {{knowledge_context}}',
            'output_schema' => ['type' => 'object', 'required' => ['topics']],
            'variables' => ['target_count', 'cluster', 'audience', 'existing_topics', 'knowledge_context'],
            'status' => \App\Models\AiPromptTemplateVersion::STATUS_ACTIVE,
        ]);

        $template->update(['active_version_id' => $version->id]);
    }
}
