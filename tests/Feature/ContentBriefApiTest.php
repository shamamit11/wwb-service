<?php

namespace Tests\Feature;

use App\AI\Enums\BlogDraftGenerationMode;
use App\Infrastructure\Ai\Contracts\AiClient;
use App\Infrastructure\Ai\Data\AiUsageData;
use App\Infrastructure\Ai\Data\GenerateTextRequest;
use App\Infrastructure\Ai\Data\TextGenerationResult;
use App\Infrastructure\Ai\Exceptions\AiCallFailedException;
use App\Jobs\AI\GenerateContentBriefJob;
use App\Jobs\AI\GenerateBlogDraftJob;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Models\KnowledgeBaseEntry;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContentBriefApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_high_priority_topics_auto_approve_generated_briefs_and_queue_draft_generation(): void
    {
        Queue::fake();

        config()->set('app.url', 'https://widewebblog.test');
        config()->set('ai.service.default_provider', 'openai');
        config()->set('ai.service.providers.openai.text_model', 'gpt-5-mini');

        $template = AiPromptTemplate::query()->create([
            'name' => 'Content Brief Default',
            'key' => 'content_brief_default',
            'type' => AiPromptTemplate::TYPE_CONTENT_BRIEF,
            'description' => 'Default brief prompt.',
            'status' => AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $version = AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $template->id,
            'version' => 1,
            'system_prompt' => 'Build a structured brief for {{topic_title}} in {{cluster}}.',
            'user_prompt' => 'Knowledge {{knowledge_context}} Existing {{existing_post_context}} Links {{internal_link_context}}',
            'output_schema' => ['type' => 'object', 'required' => ['recommended_title', 'outline', 'heading_structure']],
            'variables' => ['topic_title', 'cluster', 'knowledge_context', 'existing_post_context', 'internal_link_context'],
            'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
        ]);

        $template->update(['active_version_id' => $version->id]);

        $admin = User::factory()->create(['is_admin' => true]);

        \App\Models\Category::query()->create([
            'name' => 'Content Marketing',
            'slug' => 'content-marketing',
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'title' => 'Editorial QA',
            'slug' => 'editorial-qa',
            'entry_type' => KnowledgeBaseEntry::TYPE_REFERENCE,
            'status' => KnowledgeBaseEntry::STATUS_ACTIVE,
            'summary' => 'Use explicit editorial QA gates.',
            'content_markdown' => 'Detailed QA notes.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => null,
        ]);

        $fakeClient = new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                return new TextGenerationResult(
                    content: json_encode([
                        'recommended_title' => 'AI Editorial Checklists for Content Teams',
                        'slug' => 'ai-editorial-checklists-for-content-teams',
                        'meta_title' => 'AI Editorial Checklists for Content Teams',
                        'meta_description' => 'A structured content brief for editorial review checklists.',
                        'outline' => [
                            ['heading' => 'Why AI review checklists matter', 'purpose' => 'Frame the workflow'],
                            ['heading' => 'How to operationalize the checklist', 'purpose' => 'Explain the implementation'],
                        ],
                        'heading_structure' => [
                            'Why AI review checklists matter',
                            'How to operationalize the checklist',
                        ],
                        'faq_suggestions' => [
                            ['question' => 'What belongs in an AI review checklist?', 'answer_focus' => 'Review criteria'],
                        ],
                        'internal_link_suggestions' => [],
                        'image_ideas' => ['Workflow diagram'],
                        'alt_text_suggestions' => ['Workflow diagram for editorial review'],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'openai',
                    model: 'gpt-5-mini',
                    usage: new AiUsageData(promptTokens: 80, completionTokens: 60),
                );
            }
        };

        $this->app->instance(AiClient::class, $fakeClient);

        $topic = ContentTopic::query()->create([
            'title' => 'AI Editorial Checklists for Content Teams',
            'slug' => 'ai-editorial-checklists-for-content-teams',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'ai editorial checklist',
            'secondary_keywords' => ['content operations', 'editorial workflow'],
            'search_intent' => 'informational',
            'priority_score' => '91.50',
            'difficulty_note' => 'Strong operational angle.',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_SUGGESTED,
            'notes' => 'Priority draft candidate.',
        ]);

        $approvedTopic = app(\App\Modules\ContentTopics\Services\AutoAdvanceHighPriorityTopicService::class)->handle($topic);

        $this->assertSame(ContentTopic::STATUS_APPROVED, $approvedTopic->status);

        $briefJob = AiJob::query()->where('type', AiPromptTemplate::TYPE_CONTENT_BRIEF)->latest('id')->firstOrFail();

        Queue::assertPushed(GenerateContentBriefJob::class, function (GenerateContentBriefJob $queuedJob) use ($briefJob): bool {
            return $queuedJob->aiJobId === (int) $briefJob->id
                && $queuedJob->queue === 'ai';
        });

        app(\App\Modules\Ai\Services\AiWorkflowOrchestrator::class)->runQueuedContentBrief((int) $briefJob->id);

        $brief = ContentBrief::query()->where('content_topic_id', $topic->id)->firstOrFail();

        $this->assertSame(ContentBrief::STATUS_APPROVED, $brief->status);

        $draftJob = AiJob::query()->where('type', AiPromptTemplate::TYPE_BLOG_WRITER)->latest('id')->firstOrFail();

        $this->assertDatabaseHas('ai_jobs', [
            'id' => $draftJob->id,
            'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
            'status' => AiJob::STATUS_QUEUED,
            'entity_type' => 'content_brief',
            'entity_id' => $brief->id,
        ]);

        Queue::assertPushed(GenerateBlogDraftJob::class, function (GenerateBlogDraftJob $queuedJob) use ($draftJob): bool {
            return $queuedJob->aiJobId === (int) $draftJob->id
                && $queuedJob->queue === 'ai';
        });
    }

    public function test_admin_content_brief_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/content-briefs')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_generate_review_update_and_approve_content_briefs_from_approved_topics(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'AI Editorial Checklists for Content Teams',
            'slug' => 'ai-editorial-checklists-for-content-teams',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'ai editorial checklist',
            'secondary_keywords' => ['content operations', 'editorial workflow'],
            'search_intent' => 'informational',
            'priority_score' => '88.00',
            'difficulty_note' => 'Moderate competition with practical long-tail angle.',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => 'Approved for brief generation.',
            'approved_at' => now(),
        ]);

        KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'title' => 'Editorial QA',
            'slug' => 'editorial-qa',
            'entry_type' => KnowledgeBaseEntry::TYPE_REFERENCE,
            'status' => KnowledgeBaseEntry::STATUS_ACTIVE,
            'summary' => 'Use explicit editorial QA gates.',
            'content_markdown' => 'Detailed QA notes.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => null,
        ]);

        $generateResponse = $this->withToken($token)->postJson("/api/v1/admin/content-topics/{$topic->id}/generate-brief");

        $generateResponse->assertAccepted()
            ->assertJsonPath('message', 'Content brief generation queued.')
            ->assertJsonPath('meta.ai_job_status', AiJob::STATUS_QUEUED);

        $job = AiJob::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('ai_jobs', [
            'id' => $job->id,
            'type' => AiPromptTemplate::TYPE_CONTENT_BRIEF,
            'status' => AiJob::STATUS_QUEUED,
            'entity_type' => 'content_topic',
            'entity_id' => $topic->id,
        ]);

        Queue::assertPushed(GenerateContentBriefJob::class, function (GenerateContentBriefJob $queuedJob) use ($job): bool {
            return $queuedJob->aiJobId === (int) $job->id
                && $queuedJob->queue === 'ai';
        });

        Queue::assertPushed(GenerateContentBriefJob::class, 1);
    }

    public function test_generate_brief_returns_existing_brief_when_topic_already_has_one(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'AI Editorial Checklists for Content Teams',
            'slug' => 'ai-editorial-checklists-for-content-teams',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'ai editorial checklist',
            'secondary_keywords' => ['content operations', 'editorial workflow'],
            'search_intent' => 'informational',
            'priority_score' => '88.00',
            'difficulty_note' => 'Moderate competition with practical long-tail angle.',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => 'Approved for brief generation.',
            'approved_at' => now(),
        ]);

        $brief = ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'AI Editorial Checklists for Content Teams',
            'slug' => 'ai-editorial-checklists-for-content-teams',
            'meta_title' => 'AI Editorial Checklists for Content Teams',
            'meta_description' => 'A practical brief for editorial teams building AI review checklists.',
            'primary_keyword' => 'ai editorial checklist',
            'secondary_keywords' => ['content operations', 'editorial workflow'],
            'search_intent' => 'informational',
            'outline' => [
                ['heading' => 'Why AI editorial checklists matter', 'purpose' => 'Frame the workflow'],
                ['heading' => 'A practical checklist framework', 'purpose' => 'Give the implementation plan'],
            ],
            'headings' => [
                'Why AI editorial checklists matter',
                'A practical checklist framework',
            ],
            'faq_suggestions' => [
                ['question' => 'What should an editorial AI checklist include?', 'answer_focus' => 'Review gates and policy checks'],
            ],
            'internal_link_suggestions' => [],
            'image_suggestions' => [
                ['placement' => 'hero', 'idea' => 'Checklist board', 'alt_text' => 'Editorial checklist board with AI review steps'],
            ],
            'status' => ContentBrief::STATUS_DRAFT,
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/content-topics/{$topic->id}/generate-brief")
            ->assertOk()
            ->assertJsonPath('data.id', $brief->id)
            ->assertJsonPath('data.content_topic_id', $topic->id)
            ->assertJsonPath('data.title', 'AI Editorial Checklists for Content Teams')
            ->assertJsonPath('data.status', ContentBrief::STATUS_DRAFT)
            ->assertJsonPath('data.primary_keyword', 'ai editorial checklist')
            ->assertJsonPath('data.search_intent', 'informational')
            ->assertJsonCount(2, 'data.headings')
            ->assertJsonCount(1, 'data.faq_suggestions')
            ->assertJsonCount(1, 'data.image_suggestions')
            ->assertJsonPath('data.can_generate_draft', false);
    }

    public function test_admin_can_review_update_and_approve_content_briefs(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $category = \App\Models\Category::query()->create([
            'name' => 'Content Marketing',
            'slug' => 'content-marketing',
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $topic = ContentTopic::query()->create([
            'title' => 'AI Editorial Checklists for Content Teams',
            'slug' => 'ai-editorial-checklists-for-content-teams',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'ai editorial checklist',
            'secondary_keywords' => ['content operations', 'editorial workflow'],
            'search_intent' => 'informational',
            'priority_score' => '88.00',
            'difficulty_note' => 'Moderate competition with practical long-tail angle.',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => 'Approved for brief generation.',
            'approved_at' => now(),
        ]);

        $brief = ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'AI Editorial Checklists for Content Teams',
            'slug' => 'ai-editorial-checklists-for-content-teams',
            'meta_title' => 'AI Editorial Checklists for Content Teams',
            'meta_description' => 'A practical brief for editorial teams building AI review checklists.',
            'primary_keyword' => 'ai editorial checklist',
            'secondary_keywords' => ['content operations', 'editorial workflow'],
            'search_intent' => 'informational',
            'outline' => [
                ['heading' => 'Why AI editorial checklists matter', 'purpose' => 'Frame the workflow'],
                ['heading' => 'A practical checklist framework', 'purpose' => 'Give the implementation plan'],
            ],
            'headings' => [
                'Why AI editorial checklists matter',
                'A practical checklist framework',
            ],
            'faq_suggestions' => [
                ['question' => 'What should an editorial AI checklist include?', 'answer_focus' => 'Review gates and policy checks'],
            ],
            'internal_link_suggestions' => [],
            'image_suggestions' => [
                ['placement' => 'hero', 'idea' => 'Checklist board', 'alt_text' => 'Editorial checklist board with AI review steps'],
            ],
            'status' => ContentBrief::STATUS_DRAFT,
        ]);

        $briefId = (int) $brief->id;

        $this->withToken($token)->getJson("/api/v1/admin/content-briefs/{$briefId}")
            ->assertOk()
            ->assertJsonPath('data.topic.id', $topic->id)
            ->assertJsonPath('data.topic.status', ContentTopic::STATUS_APPROVED);

        $this->withToken($token)->patchJson("/api/v1/admin/content-briefs/{$briefId}", [
            'meta_title' => 'AI Editorial Checklists for Content Teams',
            'meta_description' => 'A practical brief for editorial teams building AI review checklists.',
            'headings' => [
                'Why AI editorial checklists matter',
                'A practical checklist framework',
            ],
            'outline' => [
                ['heading' => 'Why AI editorial checklists matter', 'purpose' => 'Frame the workflow'],
                ['heading' => 'A practical checklist framework', 'purpose' => 'Give the implementation plan'],
            ],
            'faq_suggestions' => [
                ['question' => 'What should an editorial AI checklist include?', 'answer_focus' => 'Review gates and policy checks'],
            ],
            'image_suggestions' => [
                ['placement' => 'hero', 'idea' => 'Checklist board', 'alt_text' => 'Editorial checklist board with AI review steps'],
            ],
            'status' => ContentBrief::STATUS_REJECTED,
        ])->assertOk()
            ->assertJsonPath('data.status', ContentBrief::STATUS_REJECTED)
            ->assertJsonPath('data.headings.1', 'A practical checklist framework')
            ->assertJsonPath('data.image_suggestions.0.alt_text', 'Editorial checklist board with AI review steps');

        $this->withToken($token)->postJson("/api/v1/admin/content-briefs/{$briefId}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', ContentBrief::STATUS_APPROVED)
            ->assertJsonPath('data.can_generate_draft', true);

        $this->assertDatabaseHas('content_briefs', [
            'id' => $briefId,
            'content_topic_id' => $topic->id,
            'status' => ContentBrief::STATUS_APPROVED,
            'primary_keyword' => 'ai editorial checklist',
        ]);

        $job = AiJob::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('ai_jobs', [
            'id' => $job->id,
            'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
            'status' => AiJob::STATUS_QUEUED,
            'entity_type' => 'content_brief',
            'entity_id' => $briefId,
            'attempts' => 1,
        ]);

        $this->assertSame($category->id, $job->input_payload['category_id'] ?? null);

        Queue::assertPushed(GenerateBlogDraftJob::class, function (GenerateBlogDraftJob $queuedJob) use ($job): bool {
            return $queuedJob->aiJobId === (int) $job->id
                && $queuedJob->queue === 'ai';
        });
    }

    public function test_approving_brief_skips_auto_draft_queue_when_no_active_category_exists(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'AI Editorial Checklists for Content Teams',
            'slug' => 'ai-editorial-checklists-for-content-teams',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'ai editorial checklist',
            'secondary_keywords' => ['content operations', 'editorial workflow'],
            'search_intent' => 'informational',
            'priority_score' => '88.00',
            'difficulty_note' => 'Moderate competition with practical long-tail angle.',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => 'Approved for brief generation.',
            'approved_at' => now(),
        ]);

        $brief = ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'AI Editorial Checklists for Content Teams',
            'slug' => 'ai-editorial-checklists-for-content-teams',
            'meta_title' => 'AI Editorial Checklists for Content Teams',
            'meta_description' => 'A practical brief for editorial teams building AI review checklists.',
            'primary_keyword' => 'ai editorial checklist',
            'secondary_keywords' => ['content operations', 'editorial workflow'],
            'search_intent' => 'informational',
            'outline' => [
                ['heading' => 'Why AI editorial checklists matter', 'purpose' => 'Frame the workflow'],
            ],
            'headings' => [
                'Why AI editorial checklists matter',
            ],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_DRAFT,
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/content-briefs/{$brief->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', ContentBrief::STATUS_APPROVED)
            ->assertJsonPath('data.can_generate_draft', true);

        $this->assertDatabaseCount('ai_jobs', 0);
        Queue::assertNothingPushed();
    }

    public function test_content_brief_generation_is_rejected_for_non_approved_topics(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'Agent Review Loops',
            'slug' => 'agent-review-loops',
            'cluster' => ContentTopic::CLUSTER_DEVELOPER_AI,
            'primary_keyword' => 'agent review loops',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '72.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_SUGGESTED,
            'notes' => null,
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/content-topics/{$topic->id}/generate-brief")
            ->assertStatus(409)
            ->assertJsonPath('error_code', 'CONFLICT')
            ->assertJsonPath('errors.status.0', ContentTopic::STATUS_SUGGESTED)
            ->assertJsonPath('errors.action.0', 'generate-brief');
    }

    public function test_generate_brief_reuses_existing_active_job_when_one_is_already_queued(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'Debug Brief Generation Topic',
            'slug' => 'debug-brief-generation-topic',
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'primary_keyword' => 'debug brief generation',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '50.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => 'Debug provider failure handling.',
            'approved_at' => now(),
        ]);

        $job = AiJob::query()->create([
            'type' => AiPromptTemplate::TYPE_CONTENT_BRIEF,
            'status' => AiJob::STATUS_QUEUED,
            'entity_type' => 'content_topic',
            'entity_id' => $topic->id,
            'input_payload' => [
                'content_topic_id' => $topic->id,
                'prompt_template_key' => null,
            ],
            'attempts' => 1,
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/content-topics/{$topic->id}/generate-brief")
            ->assertAccepted()
            ->assertJsonPath('message', 'Content brief generation queued.')
            ->assertJsonPath('meta.ai_job_id', $job->id)
            ->assertJsonPath('meta.ai_job_status', AiJob::STATUS_QUEUED);

        $this->assertDatabaseCount('ai_jobs', 1);
    }

    public function test_admin_can_queue_draft_generation_from_an_approved_brief(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'AI Editorial Checklists for Content Teams',
            'slug' => 'ai-editorial-checklists-for-content-teams',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'ai editorial checklist',
            'secondary_keywords' => ['content operations', 'editorial workflow'],
            'search_intent' => 'informational',
            'priority_score' => '88.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => 'Approved for brief generation.',
            'approved_at' => now(),
        ]);

        $brief = ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'AI Editorial Checklists for Content Teams',
            'slug' => 'ai-editorial-checklists-for-content-teams',
            'meta_title' => 'AI Editorial Checklists for Content Teams',
            'meta_description' => 'Structured brief for editorial checklists.',
            'primary_keyword' => 'ai editorial checklist',
            'secondary_keywords' => ['content operations', 'editorial workflow'],
            'search_intent' => 'informational',
            'outline' => [['heading' => 'Why this matters', 'purpose' => 'Frame the workflow']],
            'headings' => ['Why this matters'],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $category = \App\Models\Category::query()->create([
            'name' => 'AI Workflows',
            'slug' => 'ai-workflows',
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/content-briefs/{$brief->id}/generate-draft", [
            'category_id' => $category->id,
            'generation_mode' => BlogDraftGenerationMode::Checklist->value,
        ])->assertAccepted()
            ->assertJsonPath('data.type', AiPromptTemplate::TYPE_BLOG_WRITER)
            ->assertJsonPath('data.status', AiJob::STATUS_QUEUED)
            ->assertJsonPath('data.entity_type', 'content_brief')
            ->assertJsonPath('data.entity_id', $brief->id)
            ->assertJsonPath('data.input_payload.content_brief_id', $brief->id)
            ->assertJsonPath('data.input_payload.category_id', $category->id)
            ->assertJsonPath('data.input_payload.author_user_id', null)
            ->assertJsonPath('data.input_payload.generation_mode', BlogDraftGenerationMode::Checklist->value);

        $job = AiJob::query()->latest('id')->firstOrFail();

        Queue::assertPushed(GenerateBlogDraftJob::class, function (GenerateBlogDraftJob $queuedJob) use ($job): bool {
            return $queuedJob->aiJobId === (int) $job->id
                && $queuedJob->queue === 'ai';
        });
    }

    public function test_draft_generation_is_rejected_for_non_approved_briefs_when_no_post_exists(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'AI Search Intent Maps',
            'slug' => 'ai-search-intent-maps',
            'cluster' => ContentTopic::CLUSTER_SEO,
            'primary_keyword' => 'ai search intent',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '70.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => null,
            'approved_at' => now(),
        ]);

        $brief = ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'AI Search Intent Maps',
            'slug' => 'ai-search-intent-maps',
            'meta_title' => null,
            'meta_description' => null,
            'primary_keyword' => 'ai search intent',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'outline' => [['heading' => 'Intro', 'purpose' => 'Frame the topic']],
            'headings' => ['Intro'],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_DRAFT,
        ]);

        $category = \App\Models\Category::query()->create([
            'name' => 'SEO',
            'slug' => 'seo',
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/content-briefs/{$brief->id}/generate-draft", [
            'category_id' => $category->id,
        ])->assertStatus(409)
            ->assertJsonPath('error_code', 'CONFLICT')
            ->assertJsonPath('errors.status.0', ContentBrief::STATUS_DRAFT)
            ->assertJsonPath('errors.action.0', 'generate-draft');
    }

    public function test_content_brief_validation_errors_use_consistent_json_shape(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'AI Search Intent Maps',
            'slug' => 'ai-search-intent-maps',
            'cluster' => ContentTopic::CLUSTER_SEO,
            'primary_keyword' => 'ai search intent',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '70.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => null,
            'approved_at' => now(),
        ]);

        $brief = ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'AI Search Intent Maps',
            'slug' => 'ai-search-intent-maps',
            'meta_title' => null,
            'meta_description' => null,
            'primary_keyword' => 'ai search intent',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'outline' => [['heading' => 'Intro', 'purpose' => 'Frame the topic']],
            'headings' => ['Intro'],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_DRAFT,
        ]);

        $briefId = (int) $brief->id;

        $this->withToken($token)->patchJson("/api/v1/admin/content-briefs/{$briefId}", [
            'headings' => ['Valid', 123],
            'status' => ContentBrief::STATUS_APPROVED,
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['headings.1', 'status'],
            'meta' => ['request_id'],
            ]);
    }

    public function test_generate_draft_rejects_unknown_generation_mode(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'Mode Validation Topic',
            'slug' => 'mode-validation-topic',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'mode validation',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '65.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => null,
            'approved_at' => now(),
        ]);

        $brief = ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'Mode Validation Brief',
            'slug' => 'mode-validation-brief',
            'meta_title' => null,
            'meta_description' => null,
            'primary_keyword' => 'mode validation',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'outline' => [['heading' => 'Intro', 'purpose' => 'Frame the topic']],
            'headings' => ['Intro'],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $category = \App\Models\Category::query()->create([
            'name' => 'AI Workflows',
            'slug' => 'ai-workflows-validation',
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/content-briefs/{$brief->id}/generate-draft", [
            'category_id' => $category->id,
            'generation_mode' => 'longform_manifesto',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['generation_mode'],
                'meta' => ['request_id'],
            ]);
    }

    private function seedContentBriefPromptTemplate(): void
    {
        $template = AiPromptTemplate::query()->create([
            'name' => 'Content Brief Default',
            'key' => 'content_brief_default',
            'type' => AiPromptTemplate::TYPE_CONTENT_BRIEF,
            'description' => 'Default brief prompt.',
            'status' => AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $version = AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $template->id,
            'version' => 1,
            'system_prompt' => 'Build a structured brief for {{topic_title}}.',
            'user_prompt' => 'Knowledge {{knowledge_context}} Existing {{existing_post_context}} Links {{internal_link_context}}',
            'output_schema' => ['type' => 'object'],
            'variables' => ['topic_title', 'knowledge_context', 'existing_post_context', 'internal_link_context'],
            'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
        ]);

        $template->update(['active_version_id' => $version->id]);
    }
}
