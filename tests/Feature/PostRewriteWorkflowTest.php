<?php

namespace Tests\Feature;

use App\Infrastructure\Ai\Contracts\AiClient;
use App\Infrastructure\Ai\Data\AiUsageData;
use App\Infrastructure\Ai\Data\GenerateTextRequest;
use App\Infrastructure\Ai\Data\TextGenerationResult;
use App\Models\AiGenerationStep;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use App\Models\Category;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Models\KnowledgeBaseEntry;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\Ai\Services\RunPostRewriteService;
use App\Modules\Posts\Services\RewritePostDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostRewriteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_queued_full_draft_rewrite_updates_existing_draft_and_tracks_ai_job(): void
    {
        [, $post] = $this->seedRewriteFixture();

        $fakeClient = new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                return new TextGenerationResult(
                    content: json_encode([
                        'title' => 'AI Editorial Review Checklists Rewritten',
                        'slug' => 'ai-editorial-review-checklists-rewritten',
                        'excerpt' => 'A refreshed rewrite for editorial teams.',
                        'content_blocks' => [
                            ['block_type' => 'heading', 'sort_order' => 1, 'content' => ['text' => 'AI Editorial Review Checklists Rewritten', 'level' => 1]],
                            ['block_type' => 'paragraph', 'sort_order' => 2, 'content' => ['markdown' => 'Rewritten introduction with stronger editorial grounding.']],
                            ['block_type' => 'list', 'sort_order' => 3, 'content' => ['items' => ['Check claims', 'Validate links', 'Review tone']]],
                        ],
                        'seo_title' => 'AI Editorial Review Checklists Rewritten',
                        'meta_description' => 'A rewritten draft for AI-assisted editorial review checklists.',
                        'suggested_tags' => ['Editorial Ops'],
                        'image_placement_notes' => ['Insert a checklist graphic after the introduction.'],
                        'alt_text_suggestions' => ['Checklist graphic for editorial review workflow'],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'openai',
                    model: 'gpt-5-mini',
                    usage: new AiUsageData(promptTokens: 120, completionTokens: 140),
                );
            }
        };

        $this->app->instance(AiClient::class, $fakeClient);

        $job = app(AiJobRepository::class)->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_EDITOR,
            status: AiJob::STATUS_QUEUED,
            entityType: 'post',
            entityId: (int) $post->id,
            inputPayload: [
                'post_id' => (int) $post->id,
                'scope' => RewritePostDraftService::SCOPE_FULL_DRAFT,
                'target_block_ids' => [],
                'instructions' => 'Tighten the framing and improve the checklist flow.',
            ],
        ));

        app(RunPostRewriteService::class)->handle((int) $job->id);

        $job = AiJob::query()->findOrFail($job->id);
        $post = $post->fresh(['blocks', 'seo', 'tags']);

        $this->assertSame(AiJob::STATUS_COMPLETED, $job->status);
        $this->assertSame('AI Editorial Review Checklists Rewritten', $post->title);
        $this->assertSame('ai-editorial-review-checklists-rewritten', $post->slug);
        $this->assertSame(Post::STATUS_DRAFT, $post->status);
        $this->assertSame('DraftRewriteAgent', $post->meta['generated_by']);
        $this->assertSame((int) $job->id, $post->meta['ai_job_id']);
        $this->assertSame(1, $post->meta['rewrite_count']);
        $this->assertCount(3, $post->blocks);
        $this->assertSame('AI Editorial Review Checklists Rewritten', $post->seo?->meta_title);
        $this->assertDatabaseHas('ai_generation_steps', [
            'ai_job_id' => $job->id,
            'agent_name' => 'DraftRewriteAgent',
            'status' => AiGenerationStep::STATUS_COMPLETED,
        ]);
    }

    public function test_queued_paragraph_rewrite_only_replaces_targeted_block(): void
    {
        [, $post] = $this->seedRewriteFixture();
        $targetBlock = $post->blocks()->orderBy('sort_order')->skip(1)->firstOrFail();
        $beforeHeading = $post->blocks()->orderBy('sort_order')->firstOrFail()->plain_text_cache;
        $beforeLastParagraph = $post->blocks()->reorder('sort_order', 'desc')->firstOrFail()->content_markdown;

        $fakeClient = new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                return new TextGenerationResult(
                    content: json_encode([
                        'content_blocks' => [
                            ['block_type' => 'paragraph', 'sort_order' => 1, 'content' => ['markdown' => 'Updated paragraph with sharper editorial guidance.']],
                        ],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'openai',
                    model: 'gpt-5-mini',
                    usage: new AiUsageData(promptTokens: 80, completionTokens: 60),
                );
            }
        };

        $this->app->instance(AiClient::class, $fakeClient);

        $job = app(AiJobRepository::class)->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_EDITOR,
            status: AiJob::STATUS_QUEUED,
            entityType: 'post',
            entityId: (int) $post->id,
            inputPayload: [
                'post_id' => (int) $post->id,
                'scope' => RewritePostDraftService::SCOPE_PARAGRAPH,
                'target_block_ids' => [(int) $targetBlock->id],
                'instructions' => 'Make this paragraph more concrete.',
            ],
        ));

        app(RunPostRewriteService::class)->handle((int) $job->id);

        $post = $post->fresh(['blocks']);
        $blocks = $post->blocks->sortBy('sort_order')->values();

        $this->assertSame($beforeHeading, $blocks[0]->plain_text_cache);
        $this->assertSame('Updated paragraph with sharper editorial guidance.', $blocks[1]->content_markdown);
        $this->assertSame($beforeLastParagraph, $blocks[2]->content_markdown);
        $this->assertSame(1, $post->meta['rewrite_count']);
        $this->assertSame(RewritePostDraftService::SCOPE_PARAGRAPH, $post->meta['last_rewrite_scope']);
    }

    /**
     * @return array{0: User, 1: Post}
     */
    private function seedRewriteFixture(): array
    {
        config()->set('app.url', 'https://widewebblog.test');
        config()->set('ai.service.default_provider', 'openai');
        config()->set('ai.service.providers.openai.text_model', 'gpt-5-mini');
        config()->set('ai.service.pricing.default_currency', 'USD');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.input_per_1k_tokens', '0.001');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.output_per_1k_tokens', '0.003');

        $template = AiPromptTemplate::query()->create([
            'name' => 'Post Rewrite Default',
            'key' => 'post_rewrite_default',
            'type' => AiPromptTemplate::TYPE_EDITOR,
            'description' => 'Default draft rewrite prompt.',
            'status' => AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $version = AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $template->id,
            'version' => 1,
            'system_prompt' => 'Rewrite the requested draft scope.',
            'user_prompt' => 'Scope {{scope}} Instructions {{instructions}} Existing {{existing_content_blocks}} Targets {{target_blocks}} Knowledge {{knowledge_context}}',
            'output_schema' => ['type' => 'object', 'required' => ['content_blocks']],
            'variables' => ['scope', 'instructions', 'existing_content_blocks', 'target_blocks', 'knowledge_context'],
            'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
        ]);
        $template->update(['active_version_id' => $version->id]);

        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::query()->create([
            'name' => 'AI Workflows',
            'slug' => 'ai-workflows',
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        Tag::query()->create([
            'name' => 'Editorial Ops',
            'slug' => 'editorial-ops',
            'description' => null,
            'is_active' => true,
        ]);
        $topic = ContentTopic::query()->create([
            'title' => 'AI Editorial Review Checklists',
            'slug' => 'ai-editorial-review-checklists',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'ai editorial review checklist',
            'secondary_keywords' => ['editorial ops'],
            'search_intent' => 'informational',
            'priority_score' => '92.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_USED,
            'notes' => 'Already used for drafting.',
            'approved_at' => now(),
            'used_at' => now(),
        ]);
        $brief = ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'AI Editorial Review Checklists for Content Teams',
            'slug' => 'ai-editorial-review-checklists-for-content-teams',
            'meta_title' => 'AI Editorial Review Checklists for Content Teams',
            'meta_description' => 'Structured brief for editorial review checklists.',
            'primary_keyword' => 'ai editorial review checklist',
            'secondary_keywords' => ['editorial ops'],
            'search_intent' => 'informational',
            'outline' => [['heading' => 'Why review checklists matter', 'purpose' => 'Frame the workflow']],
            'headings' => ['Why review checklists matter'],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_USED,
            'approved_at' => now(),
        ]);
        KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'title' => 'Editorial QA',
            'slug' => 'editorial-qa',
            'entry_type' => KnowledgeBaseEntry::TYPE_REFERENCE,
            'status' => KnowledgeBaseEntry::STATUS_ACTIVE,
            'summary' => 'Use explicit QA gates before publication.',
            'content_markdown' => 'Detailed editorial QA notes.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => null,
        ]);
        $post = Post::query()->create([
            'author_user_id' => $admin->id,
            'category_id' => $category->id,
            'template_id' => null,
            'featured_media_id' => null,
            'title' => 'AI Editorial Review Checklists for Content Teams',
            'slug' => 'ai-editorial-review-checklists-for-content-teams',
            'excerpt' => 'Original excerpt for editorial teams.',
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
            'scheduled_for' => null,
            'content_version' => 1,
            'reading_time_minutes' => 5,
            'word_count' => 500,
            'is_featured' => false,
            'meta' => [
                'source_content_brief_id' => (int) $brief->id,
                'source_content_topic_id' => (int) $topic->id,
                'primary_keyword' => 'ai editorial review checklist',
                'secondary_keywords' => ['editorial ops'],
                'search_intent' => 'informational',
                'markdown_body' => "# AI Editorial Review Checklists for Content Teams\n\nOriginal introduction.\n\nOriginal closing paragraph.",
                'generated_by' => 'BlogWriterAgent',
            ],
        ]);
        $post->blocks()->createMany([
            [
                'block_type' => 'heading',
                'sort_order' => 1,
                'content_markdown' => '# AI Editorial Review Checklists for Content Teams',
                'plain_text_cache' => 'AI Editorial Review Checklists for Content Teams',
                'settings' => ['level' => 1],
            ],
            [
                'block_type' => 'paragraph',
                'sort_order' => 2,
                'content_markdown' => 'Original introduction.',
                'plain_text_cache' => 'Original introduction.',
                'settings' => [],
            ],
            [
                'block_type' => 'paragraph',
                'sort_order' => 3,
                'content_markdown' => 'Original closing paragraph.',
                'plain_text_cache' => 'Original closing paragraph.',
                'settings' => [],
            ],
        ]);

        return [$admin, $post->fresh(['blocks', 'seo', 'tags'])];
    }
}
