<?php

namespace Tests\Feature;

use App\Mcp\ContentMcpRegistration;
use App\Mcp\Prompts\BlogDraftPrompt;
use App\Mcp\Resources\KnowledgeBaseEntriesResource;
use App\Mcp\Resources\RecentAiJobsResource;
use App\Mcp\Servers\ContentOperationsServer;
use App\Mcp\Tools\CreateTopicSuggestionTool;
use App\Mcp\Tools\GenerateBlogDraftTool;
use App\Mcp\Tools\GenerateContentBriefTool;
use App\Mcp\Tools\GetAiJobStatusTool;
use App\Mcp\Tools\ListContentTopicsTool;
use App\Mcp\Tools\SearchKnowledgeBaseTool;
use App\Models\AiJob;
use App\Models\Category;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Models\KnowledgeBaseEntry;
use App\Models\User;
use App\Modules\Ai\Services\ContentBriefWorkflow;
use App\Modules\ContentBriefs\Data\GeneratedContentBriefData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Queue;
use Laravel\Mcp\Facades\Mcp;
use Mockery;
use Tests\TestCase;

class ContentOperationsMcpServerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_registration_is_guarded_and_route_uses_admin_middleware(): void
    {
        $originalEnv = $this->app['env'];

        $this->app['env'] = 'production';
        config()->set('ai.service.mcp.enabled', false);
        $this->assertFalse(ContentMcpRegistration::shouldRegister());

        config()->set('ai.service.mcp.enabled', true);
        $this->assertTrue(ContentMcpRegistration::shouldRegister());

        $this->app['env'] = $originalEnv;

        $route = Mcp::getWebServer(ContentMcpRegistration::path());

        $this->assertInstanceOf(Route::class, $route);
        $this->assertContains('auth:sanctum', $route->gatherMiddleware());
        $this->assertContains('can:access-admin-api', $route->gatherMiddleware());
    }

    public function test_server_tools_cover_safe_content_operations(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $this->createKnowledgeEntry($admin, [
            'title' => 'Editorial Review System',
            'slug' => 'editorial-review-system',
            'summary' => 'Review gates and editorial checks for AI-assisted content.',
            'content_markdown' => 'Use grounded references before generating briefs or drafts.',
            'metadata' => ['clusters' => ['ai_for_blogging']],
        ]);

        $approvedTopic = ContentTopic::query()->create([
            'title' => 'Editorial AI Checklists',
            'slug' => 'editorial-ai-checklists',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'editorial ai checklist',
            'secondary_keywords' => ['editorial ops'],
            'search_intent' => 'informational',
            'priority_score' => '88.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => 'Editorial references required.',
            'approved_at' => now(),
        ]);

        $briefTopic = ContentTopic::query()->create([
            'title' => 'Brief Generation Topic',
            'slug' => 'brief-generation-topic',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'brief generation topic',
            'secondary_keywords' => ['briefs'],
            'search_intent' => 'informational',
            'priority_score' => '73.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => null,
            'approved_at' => now(),
        ]);

        $duplicateTopic = ContentTopic::query()->create([
            'title' => 'Existing Suggested Topic',
            'slug' => 'existing-suggested-topic',
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'primary_keyword' => 'existing suggested topic',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '51.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_SUGGESTED,
            'notes' => null,
        ]);

        $category = Category::query()->create([
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'name' => 'AI Workflows',
            'slug' => 'ai-workflows',
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $brief = ContentBrief::query()->create([
            'content_topic_id' => $approvedTopic->id,
            'title' => 'Editorial AI Checklists for Content Teams',
            'slug' => 'editorial-ai-checklists-for-content-teams',
            'meta_title' => null,
            'meta_description' => null,
            'primary_keyword' => 'editorial ai checklist',
            'secondary_keywords' => ['editorial ops'],
            'search_intent' => 'informational',
            'outline' => [['heading' => 'Why checklists matter', 'purpose' => 'Frame the draft']],
            'headings' => ['Why checklists matter'],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        ContentOperationsServer::tool(SearchKnowledgeBaseTool::class, [
            'subject' => 'editorial ai',
            'keywords' => ['checklists'],
            'metadata_filters' => ['clusters' => ['ai_for_blogging']],
        ])->assertOk()->assertStructuredContent(function ($json): void {
            $json->where('entries.0.slug', 'editorial-review-system')
                ->where('context.0', fn (string $line): bool => str_contains($line, 'Editorial Review System'))
                ->etc();
        });

        ContentOperationsServer::tool(ListContentTopicsTool::class, [
            'status' => ContentTopic::STATUS_APPROVED,
            'search' => 'Editorial AI',
            'limit' => 5,
        ])->assertOk()->assertStructuredContent(function ($json) use ($approvedTopic): void {
            $json->where('topics.0.id', $approvedTopic->id)
                ->where('topics.0.can_generate_content_brief', true)
                ->etc();
        });

        ContentOperationsServer::tool(CreateTopicSuggestionTool::class, [
            'title' => $duplicateTopic->title,
            'slug' => $duplicateTopic->slug,
            'cluster' => $duplicateTopic->cluster,
            'primary_keyword' => $duplicateTopic->primary_keyword,
        ])->assertOk()->assertStructuredContent([
            'created' => false,
            'duplicate_check' => [
                'is_duplicate' => true,
                'matches' => ['content_topic'],
            ],
            'topic' => null,
        ]);

        $generatedBrief = ContentBrief::query()->create([
            'content_topic_id' => $briefTopic->id,
            'title' => 'Generated Brief',
            'slug' => 'generated-brief',
            'meta_title' => 'Generated Brief',
            'meta_description' => 'Grounded brief content.',
            'primary_keyword' => 'editorial ai checklist',
            'secondary_keywords' => ['editorial ops'],
            'search_intent' => 'informational',
            'outline' => [['heading' => 'Context', 'purpose' => 'Set up the brief']],
            'headings' => ['Context'],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_DRAFT,
        ]);

        $workflow = Mockery::mock(ContentBriefWorkflow::class);
        $workflow->shouldReceive('generate')
            ->once()
            ->withArgs(fn (ContentTopic $topic, ?string $template): bool => $topic->is($briefTopic) && $template === null)
            ->andReturn(new GeneratedContentBriefData($generatedBrief, true));
        $this->app->instance(ContentBriefWorkflow::class, $workflow);

        ContentOperationsServer::tool(GenerateContentBriefTool::class, [
            'content_topic_id' => $briefTopic->id,
        ])->assertOk()->assertStructuredContent(function ($json) use ($generatedBrief): void {
            $json->where('created', true)
                ->where('brief.id', $generatedBrief->id)
                ->where('brief.title', 'Generated Brief')
                ->etc();
        });

        ContentOperationsServer::tool(GenerateBlogDraftTool::class, [
            'content_brief_id' => $brief->id,
            'category_id' => $category->id,
            'author_user_id' => $admin->id,
        ])->assertOk()->assertStructuredContent(function ($json) use ($brief): void {
            $json->where('queued', true)
                ->where('job.status', AiJob::STATUS_QUEUED)
                ->where('job.entity_id', $brief->id)
                ->etc();
        });

        $jobId = (int) AiJob::query()->value('id');

        ContentOperationsServer::tool(GetAiJobStatusTool::class, [
            'ai_job_id' => $jobId,
        ])->assertOk()->assertStructuredContent(function ($json) use ($jobId, $brief): void {
            $json->where('job.id', $jobId)
                ->where('job.entity_id', $brief->id)
                ->where('job.can_retry', false)
                ->etc();
        });

        Queue::assertPushed(\App\Jobs\AI\GenerateBlogDraftJob::class, 1);
    }

    public function test_server_resources_and_prompts_return_readable_context(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->createKnowledgeEntry($admin, [
            'title' => 'Editorial Grounding Notes',
            'slug' => 'editorial-grounding-notes',
            'summary' => 'Grounding notes for AI workflows.',
        ]);

        AiJob::query()->create([
            'type' => 'blog_writer',
            'status' => AiJob::STATUS_COMPLETED,
            'entity_type' => 'content_brief',
            'entity_id' => 12,
            'provider' => 'openai',
            'model' => 'gpt-5-mini',
            'input_payload' => ['content_brief_id' => 12],
            'output_payload' => ['post_id' => 99],
            'usage_payload' => ['prompt_tokens' => 150],
            'attempts' => 1,
            'completed_at' => now(),
        ]);

        ContentOperationsServer::resource(KnowledgeBaseEntriesResource::class)
            ->assertOk()
            ->assertSee('editorial-grounding-notes');

        ContentOperationsServer::resource(RecentAiJobsResource::class)
            ->assertOk()
            ->assertSee('gpt-5-mini');

        ContentOperationsServer::prompt(BlogDraftPrompt::class, [
            'content_brief_id' => 12,
            'category_id' => 3,
        ])->assertOk()->assertSee([
            'generateBlogDraft',
            'getAiJobStatus',
            'Do not publish',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createKnowledgeEntry(User $author, array $overrides = []): KnowledgeBaseEntry
    {
        return KnowledgeBaseEntry::query()->create(array_merge([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'title' => 'Knowledge Entry',
            'slug' => 'knowledge-entry-'.fake()->unique()->slug(),
            'entry_type' => KnowledgeBaseEntry::TYPE_REFERENCE,
            'status' => KnowledgeBaseEntry::STATUS_ACTIVE,
            'summary' => 'Knowledge summary.',
            'content_markdown' => 'Knowledge markdown.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => null,
        ], $overrides));
    }
}
