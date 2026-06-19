<?php

namespace Tests\Feature;

use App\AI\Enums\BlogDraftGenerationMode;
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
use App\Modules\Ai\Services\RunBlogDraftGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateBlogDraftJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_queued_blog_draft_job_completes_and_creates_a_single_draft_post(): void
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
            'output_schema' => ['type' => 'object'],
            'variables' => ['title', 'knowledge_context', 'existing_post_context', 'internal_link_context', 'outline'],
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

        $tag = Tag::query()->create([
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
            'secondary_keywords' => ['editorial ops'],
            'search_intent' => 'informational',
            'outline' => [['heading' => 'Why review checklists matter', 'purpose' => 'Frame the problem']],
            'headings' => ['Why review checklists matter'],
            'faq_suggestions' => [['question' => 'What belongs in an AI review checklist?', 'answer_focus' => 'Key review criteria']],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
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

        $fakeClient = new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                return new TextGenerationResult(
                    content: json_encode([
                        'title' => 'AI Editorial Review Checklists for Content Teams',
                        'slug' => 'ai-editorial-review-checklists-for-content-teams',
                        'excerpt' => 'A practical draft for editorial teams reviewing AI-assisted content.',
                        'markdown_body' => "# AI Editorial Review Checklists for Content Teams\n\nUse review gates before publishing AI-assisted content.",
                        'content_blocks' => [
                            ['block_type' => 'heading', 'sort_order' => 1, 'content' => ['text' => 'AI Editorial Review Checklists for Content Teams', 'level' => 1]],
                            ['block_type' => 'paragraph', 'sort_order' => 2, 'content' => ['markdown' => 'Use review gates before publishing AI-assisted content.']],
                        ],
                        'seo_title' => 'AI Editorial Review Checklists for Content Teams',
                        'meta_description' => 'A draft article on review checklists for AI-assisted editorial workflows.',
                        'faq_suggestions' => [
                            ['question' => 'What belongs in an AI review checklist?', 'answer_markdown' => 'Accuracy, links, brand voice, and compliance checks.'],
                        ],
                        'suggested_tags' => ['Editorial Ops'],
                        'image_placement_notes' => ['Add a workflow diagram after the introduction.'],
                        'alt_text_suggestions' => ['Workflow diagram showing AI editorial review stages'],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'openai',
                    model: 'gpt-5-mini',
                    usage: new AiUsageData(promptTokens: 140, completionTokens: 180),
                );
            }
        };

        $this->app->instance(AiClient::class, $fakeClient);

        $job = app(AiJobRepository::class)->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_BLOG_WRITER,
            status: AiJob::STATUS_QUEUED,
            entityType: 'content_brief',
            entityId: (int) $brief->id,
            inputPayload: [
                'content_brief_id' => (int) $brief->id,
                'author_user_id' => null,
                'category_id' => (int) $category->id,
                'template_id' => null,
                'featured_media_id' => null,
                'visibility' => Post::VISIBILITY_PUBLIC,
                'generation_mode' => BlogDraftGenerationMode::Checklist->value,
            ],
        ));

        app(RunBlogDraftGenerationService::class)->handle((int) $job->id);

        $job = AiJob::query()->findOrFail($job->id);
        $post = Post::query()->where('meta->source_content_brief_id', $brief->id)->firstOrFail();

        $this->assertSame(AiJob::STATUS_COMPLETED, $job->status);
        $this->assertSame(Post::STATUS_DRAFT, $post->status);
        $this->assertSame(1, $post->author_user_id);
        $this->assertSame([$tag->id], $post->tags()->pluck('tags.id')->all());
        $this->assertDatabaseHas('ai_generation_steps', [
            'ai_job_id' => $job->id,
            'agent_name' => 'BlogWriterAgent',
            'status' => AiGenerationStep::STATUS_COMPLETED,
        ]);
        $this->assertDatabaseHas('ai_generation_steps', [
            'ai_job_id' => $job->id,
            'input_payload->generation_mode' => BlogDraftGenerationMode::Checklist->value,
        ]);
        $this->assertDatabaseHas('content_briefs', [
            'id' => $brief->id,
            'status' => ContentBrief::STATUS_USED,
        ]);
        $this->assertDatabaseHas('content_topics', [
            'id' => $topic->id,
            'status' => ContentTopic::STATUS_USED,
        ]);

        $retryJob = app(AiJobRepository::class)->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_BLOG_WRITER,
            status: AiJob::STATUS_QUEUED,
            entityType: 'content_brief',
            entityId: (int) $brief->id,
            inputPayload: [
                'content_brief_id' => (int) $brief->id,
                'author_user_id' => null,
                'category_id' => (int) $category->id,
                'template_id' => null,
                'featured_media_id' => null,
                'visibility' => Post::VISIBILITY_PUBLIC,
                'generation_mode' => BlogDraftGenerationMode::Checklist->value,
            ],
            attempts: 2,
            retryOfAiJobId: (int) $job->id,
        ));

        app(RunBlogDraftGenerationService::class)->handle((int) $retryJob->id);

        $retryJob = AiJob::query()->findOrFail($retryJob->id);
        $this->assertSame(AiJob::STATUS_COMPLETED, $retryJob->status);
        $this->assertSame(1, Post::query()->where('meta->source_content_brief_id', $brief->id)->count());
    }
}
