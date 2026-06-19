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
use App\Models\Post;
use App\Models\SeoMetadata;
use App\Models\User;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\Ai\Services\RunPostMetadataSuggestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostMetadataSuggestionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_queued_metadata_suggestion_job_completes_without_mutating_post_or_seo_records(): void
    {
        [$post, $seo] = $this->seedMetadataSuggestionFixture();

        $fakeClient = new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                return new TextGenerationResult(
                    content: json_encode([
                        'title' => 'Sharper AI Editorial Review Checklists',
                        'excerpt' => 'A crisper draft summary for editorial teams.',
                        'meta_title' => 'Sharper AI Editorial Review Checklists',
                        'meta_description' => 'Improved metadata suggestion for AI editorial review checklists.',
                        'focus_keyword' => 'ai editorial review checklist',
                        'schema_hints' => ['Article', 'FAQPage'],
                        'rationale' => 'Keeps the keyword prominent while improving click-through clarity.',
                    ], JSON_THROW_ON_ERROR),
                    provider: 'openai',
                    model: 'gpt-5-mini',
                    usage: new AiUsageData(promptTokens: 90, completionTokens: 70),
                );
            }
        };

        $this->app->instance(AiClient::class, $fakeClient);

        $job = app(AiJobRepository::class)->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_SEO_OPTIMIZER,
            status: AiJob::STATUS_QUEUED,
            entityType: 'post',
            entityId: (int) $post->id,
            inputPayload: [
                'post_id' => (int) $post->id,
                'instructions' => 'Improve title CTR and tighten the excerpt.',
            ],
        ));

        app(RunPostMetadataSuggestionService::class)->handle((int) $job->id);

        $job = AiJob::query()->findOrFail($job->id);
        $post = $post->fresh(['seo']);
        $seo = $seo->fresh();

        $this->assertSame(AiJob::STATUS_COMPLETED, $job->status);
        $this->assertSame('Sharper AI Editorial Review Checklists', $job->output_payload['title']);
        $this->assertSame('Improved metadata suggestion for AI editorial review checklists.', $job->output_payload['meta_description']);
        $this->assertSame('AI Editorial Review Checklists for Content Teams', $post->title);
        $this->assertSame('Original excerpt for editorial teams.', $post->excerpt);
        $this->assertSame('Existing SEO Title', $seo->meta_title);
        $this->assertSame('Existing SEO Description', $seo->meta_description);
        $this->assertDatabaseHas('ai_generation_steps', [
            'ai_job_id' => $job->id,
            'agent_name' => 'MetadataSuggestionAgent',
            'status' => AiGenerationStep::STATUS_COMPLETED,
        ]);
    }

    /**
     * @return array{0: Post, 1: SeoMetadata}
     */
    private function seedMetadataSuggestionFixture(): array
    {
        config()->set('app.url', 'https://widewebblog.test');
        config()->set('ai.service.default_provider', 'openai');
        config()->set('ai.service.providers.openai.text_model', 'gpt-5-mini');
        config()->set('ai.service.pricing.default_currency', 'USD');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.input_per_1k_tokens', '0.001');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.output_per_1k_tokens', '0.003');

        $template = AiPromptTemplate::query()->create([
            'name' => 'Post Metadata Suggestion Default',
            'key' => 'post_metadata_suggestion_default',
            'type' => AiPromptTemplate::TYPE_SEO_OPTIMIZER,
            'description' => 'Default metadata suggestion prompt.',
            'status' => AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $version = AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $template->id,
            'version' => 1,
            'system_prompt' => 'Suggest review-only metadata improvements.',
            'user_prompt' => 'Title {{post_title}} Excerpt {{post_excerpt}} Keyword {{primary_keyword}} Existing {{existing_meta_title}} {{existing_meta_description}} Body {{existing_markdown_body}}',
            'output_schema' => ['type' => 'object', 'required' => ['meta_title', 'meta_description', 'focus_keyword']],
            'variables' => ['post_title', 'post_excerpt', 'primary_keyword', 'existing_meta_title', 'existing_meta_description', 'existing_markdown_body'],
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
                'primary_keyword' => 'ai editorial review checklist',
                'secondary_keywords' => ['editorial ops'],
                'markdown_body' => "# AI Editorial Review Checklists for Content Teams\n\nOriginal body copy.",
            ],
        ]);
        $seo = SeoMetadata::query()->create([
            'seoable_type' => Post::class,
            'seoable_id' => $post->id,
            'meta_title' => 'Existing SEO Title',
            'meta_description' => 'Existing SEO Description',
            'canonical_url' => null,
            'robots_index' => true,
            'robots_follow' => true,
            'og_title' => 'Existing SEO Title',
            'og_description' => 'Existing SEO Description',
            'og_image_media_id' => null,
            'schema_type' => null,
            'schema_payload' => null,
            'focus_keyword' => 'editorial review process',
        ]);

        return [$post, $seo];
    }
}
