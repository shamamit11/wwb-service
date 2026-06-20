<?php

namespace Tests\Feature;

use App\AI\Agents\TopicDiscoveryAgent;
use App\AI\DTO\TopicDiscoveryInput;
use App\Infrastructure\Ai\Contracts\AiClient;
use App\Infrastructure\Ai\Data\AiUsageData;
use App\Infrastructure\Ai\Data\GenerateTextRequest;
use App\Infrastructure\Ai\Data\TextGenerationResult;
use App\Infrastructure\Ai\Exceptions\AiCallFailedException;
use App\Models\AiGenerationStep;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use App\Models\Category;
use App\Models\ContentTopic;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class TopicDiscoveryAgentTest extends TestCase
{
    use RefreshDatabase;

    public function test_topic_discovery_agent_renders_prompt_skips_duplicates_saves_suggestions_and_tracks_ai_workflow(): void
    {
        Queue::fake();

        config()->set('ai.service.default_provider', 'openai');
        config()->set('ai.service.providers.openai.text_model', 'gpt-5-mini');
        config()->set('ai.service.pricing.default_currency', 'USD');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.input_per_1k_tokens', '0.001');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.output_per_1k_tokens', '0.003');

        $template = AiPromptTemplate::query()->create([
            'name' => 'Topic Discovery Default',
            'key' => 'topic_discovery_default',
            'type' => AiPromptTemplate::TYPE_TOPIC_DISCOVERY,
            'description' => 'Default topic discovery prompt.',
            'status' => AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $version = AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $template->id,
            'version' => 1,
            'system_prompt' => 'Discover {{target_count}} topics for the {{cluster}} cluster.',
            'user_prompt' => 'Audience: {{audience}}. Existing: {{existing_topics}}. Knowledge: {{knowledge_context}}.',
            'output_schema' => ['type' => 'object', 'required' => ['topics']],
            'variables' => ['target_count', 'cluster', 'audience', 'existing_topics', 'knowledge_context'],
            'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
        ]);

        $template->update(['active_version_id' => $version->id]);

        ContentTopic::query()->create([
            'title' => 'Prompt Versioning for Teams',
            'slug' => 'prompt-versioning-for-teams',
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'primary_keyword' => 'prompt versioning',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '75.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_SUGGESTED,
            'notes' => null,
        ]);

        $author = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'AI',
            'slug' => 'ai',
            'created_by_user_id' => $author->id,
            'description' => null,
            'is_active' => true,
        ]);

        Post::query()->create([
            'author_user_id' => $author->id,
            'category_id' => $category->id,
            'template_id' => null,
            'featured_media_id' => null,
            'title' => 'Agent Memory Patterns',
            'slug' => 'agent-memory-patterns',
            'excerpt' => null,
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
            'scheduled_for' => null,
            'content_version' => 1,
            'reading_time_minutes' => null,
            'word_count' => null,
            'is_featured' => false,
            'meta' => null,
        ]);

        $fakeClient = new class implements AiClient
        {
            public ?GenerateTextRequest $request = null;

            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                $this->request = $request;

                return new TextGenerationResult(
                    content: json_encode([
                        'topics' => [
                            [
                                'title' => 'Prompt Versioning for Teams',
                                'primary_keyword' => 'prompt versioning',
                                'secondary_keywords' => ['prompt ops'],
                                'search_intent' => 'informational',
                                'priority_score' => 88,
                                'difficulty_note' => 'Duplicate on purpose.',
                                'summary' => 'Existing topic that should be skipped.',
                            ],
                            [
                                'title' => 'Agent Memory Patterns',
                                'primary_keyword' => 'agent memory patterns',
                                'secondary_keywords' => ['memory design'],
                                'search_intent' => 'informational',
                                'priority_score' => 81,
                                'difficulty_note' => 'Existing post duplicate.',
                                'summary' => 'Existing post that should be skipped.',
                            ],
                            [
                                'title' => 'AI Tool Audit Checklists for Editorial Teams',
                                'primary_keyword' => 'ai tool audit checklist',
                                'secondary_keywords' => ['editorial ops', 'ai stack audit'],
                                'search_intent' => 'informational',
                                'priority_score' => 93.5,
                                'difficulty_note' => 'Competitive but specific.',
                                'summary' => 'Practical checklist-driven topic.',
                            ],
                        ],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'openai',
                    model: 'gpt-5-mini',
                    usage: new AiUsageData(promptTokens: 120, completionTokens: 60),
                );
            }
        };

        $this->app->instance(AiClient::class, $fakeClient);

        $result = app(TopicDiscoveryAgent::class)->run(new TopicDiscoveryInput(
            cluster: ContentTopic::CLUSTER_AI_TOOLS,
            targetCount: 3,
            audience: 'Editorial leads running AI content systems',
            existingTopics: ['Prompt Versioning for Teams'],
            knowledgeContext: ['Keep prompts versioned.', 'Favor auditability over speed.'],
        ));

        $this->assertTrue($result->isSuccessful());
        $this->assertCount(3, $result->parsedResponse?->topics ?? []);
        $this->assertCount(1, $result->metadata['saved_topic_ids']);
        $this->assertCount(2, $result->metadata['skipped_duplicates']);
        $this->assertSame('openai', $result->provider);
        $this->assertSame('gpt-5-mini', $result->model);
        $this->assertSame(120, $result->usage?->promptTokens);

        $this->assertNotNull($fakeClient->request);
        $this->assertStringContainsString('Discover 3 topics for the ai_tools cluster.', $fakeClient->request?->systemPrompt ?? '');
        $this->assertStringContainsString('Editorial leads running AI content systems', $fakeClient->request?->prompt ?? '');
        $this->assertStringContainsString('Keep prompts versioned.', $fakeClient->request?->prompt ?? '');

        $savedTopicId = $result->metadata['saved_topic_ids'][0];

        $this->assertDatabaseHas('content_topics', [
            'id' => $savedTopicId,
            'title' => 'AI Tool Audit Checklists for Editorial Teams',
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'primary_keyword' => 'ai tool audit checklist',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
        ]);

        $savedTopic = ContentTopic::query()->findOrFail($savedTopicId);
        $this->assertStringContainsString('AI summary: Practical checklist-driven topic.', (string) $savedTopic->notes);
        $this->assertStringContainsString('Audience: Editorial leads running AI content systems', (string) $savedTopic->notes);

        $jobId = $result->metadata['job_id'];
        $stepId = $result->metadata['step_id'];

        $this->assertDatabaseHas('ai_jobs', [
            'id' => $jobId,
            'type' => AiPromptTemplate::TYPE_TOPIC_DISCOVERY,
            'status' => AiJob::STATUS_COMPLETED,
            'entity_type' => 'content_topic_batch',
            'provider' => 'openai',
            'model' => 'gpt-5-mini',
        ]);

        $this->assertDatabaseHas('ai_generation_steps', [
            'id' => $stepId,
            'ai_job_id' => $jobId,
            'agent_name' => 'TopicDiscoveryAgent',
            'status' => AiGenerationStep::STATUS_COMPLETED,
        ]);

        $job = AiJob::query()->findOrFail($jobId);
        $this->assertSame(1, count($job->output_payload['saved_topic_ids']));
        $this->assertSame(2, count($job->output_payload['skipped_duplicates']));

        $this->assertDatabaseCount('ai_job_costs', 2);
    }

    public function test_topic_discovery_agent_accepts_markdown_fenced_json_output(): void
    {
        Queue::fake();

        config()->set('ai.service.default_provider', 'openai');
        config()->set('ai.service.providers.openai.text_model', 'gpt-5-mini');

        $template = AiPromptTemplate::query()->create([
            'name' => 'Topic Discovery Default',
            'key' => 'topic_discovery_default',
            'type' => AiPromptTemplate::TYPE_TOPIC_DISCOVERY,
            'description' => 'Default topic discovery prompt.',
            'status' => AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $version = AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $template->id,
            'version' => 1,
            'system_prompt' => 'Return JSON only.',
            'user_prompt' => 'Cluster {{cluster}}',
            'output_schema' => ['type' => 'object', 'required' => ['topics']],
            'variables' => ['cluster'],
            'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
        ]);

        $template->update(['active_version_id' => $version->id]);

        $fakeClient = new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                return new TextGenerationResult(
                    content: <<<'TEXT'
Here is the result:

```json
{"topics":[{"title":"AI Topic Monitoring for Editorial Teams","primary_keyword":"ai topic monitoring","secondary_keywords":["editorial observability"],"search_intent":"informational","priority_score":87,"difficulty_note":"Manageable.","summary":"A valid topic wrapped in markdown fences."}]}
```
TEXT,
                    provider: 'openai',
                    model: 'gpt-5-mini',
                    usage: new AiUsageData(promptTokens: 40, completionTokens: 25),
                );
            }
        };

        $this->app->instance(AiClient::class, $fakeClient);

        $result = app(TopicDiscoveryAgent::class)->run(new TopicDiscoveryInput(
            cluster: ContentTopic::CLUSTER_AI_TOOLS,
            targetCount: 1,
        ));

        $this->assertTrue($result->isSuccessful());
        $this->assertSame('AI Topic Monitoring for Editorial Teams', $result->parsedResponse?->topics[0]->title);
        $this->assertCount(1, $result->metadata['saved_topic_ids']);
    }

    public function test_topic_discovery_agent_persists_underlying_provider_error_details(): void
    {
        $template = AiPromptTemplate::query()->create([
            'name' => 'Topic Discovery Default',
            'key' => 'topic_discovery_default',
            'type' => AiPromptTemplate::TYPE_TOPIC_DISCOVERY,
            'description' => 'Default topic discovery prompt.',
            'status' => AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $version = AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $template->id,
            'version' => 1,
            'system_prompt' => 'Return JSON only.',
            'user_prompt' => 'Cluster {{cluster}}',
            'output_schema' => ['type' => 'object', 'required' => ['topics']],
            'variables' => ['cluster'],
            'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
        ]);

        $template->update(['active_version_id' => $version->id]);

        $fakeClient = new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                throw AiCallFailedException::fromThrowable(new RuntimeException('The model [gpt-5-mini] is not available for this project.'));
            }
        };

        $this->app->instance(AiClient::class, $fakeClient);

        $result = app(TopicDiscoveryAgent::class)->run(new TopicDiscoveryInput(
            cluster: ContentTopic::CLUSTER_AI_TOOLS,
            targetCount: 1,
        ));

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'AI text generation failed: The model [gpt-5-mini] is not available for this project.',
            $result->error?->message,
        );
        $this->assertSame(
            RuntimeException::class,
            $result->error?->context['previous']['type'] ?? null,
        );

        $this->assertDatabaseHas('ai_jobs', [
            'id' => $result->metadata['job_id'],
            'status' => AiJob::STATUS_FAILED,
            'error_message' => 'AI text generation failed: The model [gpt-5-mini] is not available for this project.',
        ]);

        $this->assertDatabaseHas('ai_generation_steps', [
            'id' => $result->metadata['step_id'],
            'status' => AiGenerationStep::STATUS_FAILED,
            'error_message' => 'AI text generation failed: The model [gpt-5-mini] is not available for this project.',
        ]);
    }
}
