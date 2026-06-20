<?php

namespace Tests\Feature;

use App\Infrastructure\Ai\Contracts\AiClient;
use App\Infrastructure\Ai\Data\AiUsageData;
use App\Infrastructure\Ai\Data\GenerateTextRequest;
use App\Infrastructure\Ai\Data\TextGenerationResult;
use App\AI\Enums\BlogDraftGenerationMode;
use App\Models\AiGenerationStep;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use App\Models\Category;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Models\KnowledgeBaseEntry;
use App\Models\Post;
use App\Models\SeoMetadata;
use App\Models\Tag;
use App\Models\User;
use App\Modules\Posts\Exceptions\BlogDraftGenerationNotAllowedException;
use App\Modules\Posts\Services\GenerateBlogDraftFromBriefService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogWriterAgentTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_writer_agent_generates_structured_draft_saves_seo_and_tracks_ai_workflow(): void
    {
        config()->set('app.url', 'https://widewebblog.test');
        config()->set('ai.service.default_provider', 'openai');
        config()->set('ai.service.providers.openai.text_model', 'gpt-5-mini');
        config()->set('ai.service.pricing.default_currency', 'USD');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.input_per_1k_tokens', '0.001');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.output_per_1k_tokens', '0.003');

        $template = AiPromptTemplate::query()->create([
            'name' => 'Blog Writer Default',
            'key' => 'blog_writer_default',
            'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
            'description' => 'Default blog writer prompt.',
            'status' => AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $version = AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $template->id,
            'version' => 1,
            'system_prompt' => 'Write a structured draft for {{title}}.',
            'user_prompt' => 'Knowledge {{knowledge_context}} Existing {{existing_post_context}} Links {{internal_link_context}} Outline {{outline}}',
            'output_schema' => ['type' => 'object', 'required' => ['title', 'slug', 'markdown_body', 'content_blocks']],
            'variables' => ['title', 'knowledge_context', 'existing_post_context', 'internal_link_context', 'outline'],
            'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
        ]);

        $template->update(['active_version_id' => $version->id]);

        $modeTemplate = AiPromptTemplate::query()->create([
            'name' => 'Blog Writer Tutorial',
            'key' => BlogDraftGenerationMode::Tutorial->promptKey(),
            'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
            'description' => 'Tutorial-specific blog writer prompt.',
            'status' => AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $modeVersion = AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $modeTemplate->id,
            'version' => 1,
            'system_prompt' => 'Write a tutorial-style draft for {{title}}.',
            'user_prompt' => 'Mode {{generation_mode}} Guidance {{generation_mode_guidance}} Knowledge {{knowledge_context}} Existing {{existing_post_context}} Outline {{outline}}',
            'output_schema' => ['type' => 'object', 'required' => ['title', 'slug', 'markdown_body', 'content_blocks']],
            'variables' => ['title', 'generation_mode', 'generation_mode_guidance', 'knowledge_context', 'existing_post_context', 'outline'],
            'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
        ]);

        $modeTemplate->update(['active_version_id' => $modeVersion->id]);

        $author = User::factory()->create(['is_admin' => true]);
        $category = Category::query()->create([
            'name' => 'AI Workflows',
            'slug' => 'ai-workflows',
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $suggestedTag = Tag::query()->create([
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
            'secondary_keywords' => ['editorial ops', 'content review'],
            'search_intent' => 'informational',
            'priority_score' => '92.00',
            'difficulty_note' => 'Strong operational angle.',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => 'Approved for drafting.',
            'approved_at' => now(),
        ]);

        $brief = ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'AI Editorial Review Checklists for Content Teams',
            'slug' => 'ai-editorial-review-checklists-for-content-teams',
            'meta_title' => 'AI Editorial Review Checklists for Content Teams',
            'meta_description' => 'Structured brief for editorial review checklists.',
            'primary_keyword' => 'ai editorial review checklist',
            'secondary_keywords' => ['editorial ops', 'content review'],
            'search_intent' => 'informational',
            'outline' => [
                ['heading' => 'Why review checklists matter', 'purpose' => 'Frame the problem'],
                ['heading' => 'Build a workable process', 'purpose' => 'Explain the process'],
            ],
            'headings' => ['Why review checklists matter', 'Build a workable process'],
            'faq_suggestions' => [
                ['question' => 'What belongs in an AI review checklist?', 'answer_focus' => 'Key review criteria'],
            ],
            'internal_link_suggestions' => [],
            'image_suggestions' => [
                ['idea' => 'Workflow diagram', 'alt_text' => 'Diagram of editorial review workflow'],
            ],
            'status' => ContentBrief::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
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

        $relatedPost = Post::query()->create([
            'author_user_id' => $author->id,
            'category_id' => $category->id,
            'template_id' => null,
            'featured_media_id' => null,
            'title' => 'Editorial QA for Content Teams',
            'slug' => 'editorial-qa-for-content-teams',
            'excerpt' => 'Published guidance for content QA.',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => now(),
            'scheduled_for' => null,
            'content_version' => 1,
            'reading_time_minutes' => 6,
            'word_count' => 900,
            'is_featured' => false,
            'meta' => null,
        ]);

        SeoMetadata::query()->create([
            'seoable_type' => Post::class,
            'seoable_id' => $relatedPost->id,
            'meta_title' => 'Editorial QA for Content Teams',
            'meta_description' => 'Published guidance for content QA.',
            'canonical_url' => null,
            'robots_index' => true,
            'robots_follow' => true,
            'og_title' => null,
            'og_description' => null,
            'og_image_media_id' => null,
            'schema_type' => null,
            'schema_payload' => null,
            'focus_keyword' => 'editorial qa',
        ]);

        $fakeClient = new class implements AiClient
        {
            public ?GenerateTextRequest $request = null;

            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                $this->request = $request;

                return new TextGenerationResult(
                    content: json_encode([
                        'title' => 'AI Editorial Review Checklists for Content Teams',
                        'slug' => 'ai-editorial-review-checklists-for-content-teams',
                        'excerpt' => 'A practical draft for editorial teams reviewing AI-assisted content.',
                        'markdown_body' => "# AI Editorial Review Checklists for Content Teams\n\nUse review gates before publishing AI-assisted content.",
                        'content_blocks' => [
                            [
                                'block_type' => 'heading',
                                'sort_order' => 1,
                                'content' => ['text' => 'AI Editorial Review Checklists for Content Teams', 'level' => 1],
                            ],
                            [
                                'block_type' => 'paragraph',
                                'sort_order' => 2,
                                'content' => ['markdown' => 'Use review gates before publishing AI-assisted content.'],
                            ],
                            [
                                'block_type' => 'list',
                                'sort_order' => 3,
                                'content' => ['items' => ['Check claims', 'Confirm links', 'Review brand tone']],
                            ],
                        ],
                        'seo_title' => 'AI Editorial Review Checklists for Content Teams',
                        'meta_description' => 'A draft article on review checklists for AI-assisted editorial workflows.',
                        'faq_suggestions' => [
                            [
                                'question' => 'What belongs in an AI review checklist?',
                                'answer_markdown' => 'Accuracy, links, brand voice, and compliance checks.',
                            ],
                        ],
                        'suggested_tags' => ['Editorial Ops', 'AI Governance'],
                        'image_placement_notes' => ['Add a workflow diagram after the introduction.'],
                        'alt_text_suggestions' => ['Workflow diagram showing AI editorial review stages'],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'openai',
                    model: 'gpt-5-mini',
                    usage: new AiUsageData(promptTokens: 180, completionTokens: 220),
                );
            }
        };

        $this->app->instance(AiClient::class, $fakeClient);

        $generated = app(GenerateBlogDraftFromBriefService::class)->handle(
            brief: $brief->fresh('topic'),
            authorUserId: (int) $author->id,
            categoryId: (int) $category->id,
            generationMode: BlogDraftGenerationMode::Tutorial->value,
        );

        $this->assertTrue($generated->wasGenerated);
        $this->assertNotNull($fakeClient->request);
        $this->assertSame('Write a tutorial-style draft for AI Editorial Review Checklists for Content Teams.', $fakeClient->request?->systemPrompt);
        $this->assertStringContainsString(BlogDraftGenerationMode::Tutorial->guidance(), $fakeClient->request?->prompt ?? '');
        $this->assertStringContainsString('Editorial QA: Use explicit QA gates before publication.', $fakeClient->request?->prompt ?? '');
        $this->assertStringContainsString('Editorial QA for Content Teams', $fakeClient->request?->prompt ?? '');

        $post = $generated->post->refresh()->load(['tags', 'blocks', 'seo']);

        $this->assertSame(Post::STATUS_DRAFT, $post->status);
        $this->assertSame(Post::VISIBILITY_PUBLIC, $post->visibility);
        $this->assertSame('AI Editorial Review Checklists for Content Teams', $post->title);
        $this->assertSame((int) $brief->id, $post->meta['source_content_brief_id']);
        $this->assertSame(['Editorial Ops', 'AI Governance'], $post->meta['suggested_tags']);
        $this->assertSame(['Add a workflow diagram after the introduction.'], $post->meta['image_placement_notes']);
        $this->assertSame(['Workflow diagram showing AI editorial review stages'], $post->meta['alt_text_suggestions']);
        $this->assertSame([$suggestedTag->id], $post->tags->modelKeys());
        $this->assertGreaterThanOrEqual(4, $post->blocks->count());
        $this->assertSame('faq', $post->blocks->last()->block_type);
        $this->assertSame('AI Editorial Review Checklists for Content Teams', $post->seo?->meta_title);
        $this->assertSame('ai editorial review checklist', $post->seo?->focus_keyword);

        $this->assertDatabaseHas('ai_jobs', [
            'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
            'status' => AiJob::STATUS_COMPLETED,
            'entity_type' => 'content_brief',
            'entity_id' => $brief->id,
        ]);
        $this->assertDatabaseHas('ai_generation_steps', [
            'agent_name' => 'BlogWriterAgent',
            'input_payload->generation_mode' => BlogDraftGenerationMode::Tutorial->value,
        ]);

        $this->assertDatabaseHas('ai_generation_steps', [
            'agent_name' => 'BlogWriterAgent',
            'status' => AiGenerationStep::STATUS_COMPLETED,
        ]);

        $this->assertDatabaseHas('content_briefs', [
            'id' => $brief->id,
            'status' => ContentBrief::STATUS_USED,
        ]);

        $this->assertDatabaseHas('content_topics', [
            'id' => $topic->id,
            'status' => ContentTopic::STATUS_USED,
        ]);

        $retried = app(GenerateBlogDraftFromBriefService::class)->handle(
            brief: $brief->fresh('topic'),
            authorUserId: (int) $author->id,
            categoryId: (int) $category->id,
        );

        $this->assertFalse($retried->wasGenerated);
        $this->assertSame($post->id, $retried->post->id);
        $this->assertDatabaseCount('posts', 2);
        $this->assertDatabaseCount('ai_job_costs', 2);
    }

    public function test_blog_writer_generation_requires_an_approved_brief_when_no_draft_exists(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = Category::query()->create([
            'name' => 'AI Workflows',
            'slug' => 'ai-workflows',
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $topic = ContentTopic::query()->create([
            'title' => 'Draft-only topic',
            'slug' => 'draft-only-topic',
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'primary_keyword' => 'draft only topic',
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
            'title' => 'Draft Brief',
            'slug' => 'draft-brief',
            'meta_title' => null,
            'meta_description' => null,
            'primary_keyword' => 'draft brief',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'outline' => [['heading' => 'Intro', 'purpose' => 'Frame the topic']],
            'headings' => ['Intro'],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_DRAFT,
            'approved_at' => null,
        ]);

        $this->expectException(BlogDraftGenerationNotAllowedException::class);

        app(GenerateBlogDraftFromBriefService::class)->handle(
            brief: $brief->fresh('topic'),
            authorUserId: (int) $author->id,
            categoryId: (int) $category->id,
        );
    }

    public function test_blog_writer_agent_normalizes_section_blocks_into_supported_post_blocks(): void
    {
        config()->set('app.url', 'https://widewebblog.test');

        $template = AiPromptTemplate::query()->create([
            'name' => 'Blog Writer Default',
            'key' => 'blog_writer_default',
            'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
            'description' => 'Default blog writer prompt.',
            'status' => AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $version = AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $template->id,
            'version' => 1,
            'system_prompt' => 'Write a structured draft for {{title}}.',
            'user_prompt' => 'Outline {{outline}}',
            'output_schema' => ['type' => 'object', 'required' => ['title', 'slug', 'markdown_body', 'content_blocks']],
            'variables' => ['title', 'outline'],
            'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
        ]);

        $template->update(['active_version_id' => $version->id]);

        $author = User::factory()->create(['is_admin' => true]);
        $category = Category::query()->create([
            'name' => 'AI Workflows',
            'slug' => 'ai-workflows',
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $topic = ContentTopic::query()->create([
            'title' => 'AI Tools for Small Business',
            'slug' => 'ai-tools-for-small-business',
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'primary_keyword' => 'ai tools for small business',
            'secondary_keywords' => [],
            'search_intent' => 'commercial',
            'priority_score' => '85.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => 'Approved for drafting.',
            'approved_at' => now(),
        ]);

        $brief = ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'Top AI Tools for Small Businesses',
            'slug' => 'top-ai-tools-for-small-businesses',
            'meta_title' => 'Top AI Tools for Small Businesses',
            'meta_description' => 'Structured brief for small business AI tooling.',
            'primary_keyword' => 'ai tools for small business',
            'secondary_keywords' => [],
            'search_intent' => 'commercial',
            'outline' => [['heading' => 'Why small businesses should start with AI now', 'purpose' => 'Frame benefits']],
            'headings' => ['Why small businesses should start with AI now'],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $fakeClient = new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                return new TextGenerationResult(
                    content: json_encode([
                        'title' => 'Top AI Tools for Small Businesses',
                        'slug' => 'top-ai-tools-for-small-businesses',
                        'excerpt' => 'A practical guide to small business AI tooling.',
                        'markdown_body' => "# Top AI Tools for Small Businesses\n\nStart with practical wins.",
                        'content_blocks' => [
                            [
                                'block_type' => 'section',
                                'sort_order' => 1,
                                'heading' => 'Why small businesses should start with AI now',
                                'content' => [
                                    'markdown' => 'Start with practical wins.',
                                ],
                            ],
                        ],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'openai',
                    model: 'gpt-5-mini',
                    usage: new AiUsageData(promptTokens: 120, completionTokens: 160),
                );
            }
        };

        $this->app->instance(AiClient::class, $fakeClient);

        $generated = app(GenerateBlogDraftFromBriefService::class)->handle(
            brief: $brief->fresh('topic'),
            authorUserId: (int) $author->id,
            categoryId: (int) $category->id,
        );

        $blocks = $generated->post->refresh()->blocks->pluck('block_type')->all();

        $this->assertSame(['heading', 'paragraph'], $blocks);
    }
}
