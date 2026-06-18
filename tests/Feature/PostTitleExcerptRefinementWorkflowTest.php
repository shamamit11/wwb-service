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
use App\Models\User;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\Ai\Services\RunPostTitleExcerptRefinementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTitleExcerptRefinementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_queued_title_excerpt_refinement_job_completes_without_mutating_post(): void
    {
        $post = $this->seedRefinementFixture();

        $fakeClient = new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                return new TextGenerationResult(
                    content: json_encode([
                        'recommended_title' => 'AI Editorial Review Checklists That Teams Will Actually Use',
                        'recommended_excerpt' => 'A tighter draft summary that promises practical editorial review steps.',
                        'headline_variations' => [
                            'AI Editorial Review Checklists That Teams Will Actually Use',
                            'How Content Teams Can Build Better AI Review Checklists',
                            'A Practical AI Editorial Checklist for Content Teams',
                        ],
                        'excerpt_variations' => [
                            'A tighter draft summary that promises practical editorial review steps.',
                            'A concise overview of how to turn AI review into a repeatable team workflow.',
                        ],
                        'rationale' => 'The revised options are more concrete, outcome-focused, and better aligned to editorial search intent.',
                    ], JSON_THROW_ON_ERROR),
                    provider: 'openai',
                    model: 'gpt-5-mini',
                    usage: new AiUsageData(promptTokens: 75, completionTokens: 82),
                );
            }
        };

        $this->app->instance(AiClient::class, $fakeClient);

        $job = app(AiJobRepository::class)->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_EDITORIAL_REFINER,
            status: AiJob::STATUS_QUEUED,
            entityType: 'post',
            entityId: (int) $post->id,
            inputPayload: [
                'post_id' => (int) $post->id,
                'instructions' => 'Make the title clearer and the excerpt more click-worthy.',
            ],
        ));

        app(RunPostTitleExcerptRefinementService::class)->handle((int) $job->id);

        $job = AiJob::query()->findOrFail($job->id);
        $post = $post->fresh();

        $this->assertSame(AiJob::STATUS_COMPLETED, $job->status);
        $this->assertSame('AI Editorial Review Checklists That Teams Will Actually Use', $job->output_payload['recommended_title']);
        $this->assertCount(3, $job->output_payload['headline_variations']);
        $this->assertSame('AI Draft Post', $post->title);
        $this->assertSame('Original excerpt for editorial teams.', $post->excerpt);
        $this->assertDatabaseHas('ai_generation_steps', [
            'ai_job_id' => $job->id,
            'agent_name' => 'TitleExcerptRefinementAgent',
            'status' => AiGenerationStep::STATUS_COMPLETED,
        ]);
    }

    private function seedRefinementFixture(): Post
    {
        config()->set('app.url', 'https://widewebblog.test');
        config()->set('ai.service.default_provider', 'openai');
        config()->set('ai.service.providers.openai.text_model', 'gpt-5-mini');
        config()->set('ai.service.pricing.default_currency', 'USD');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.input_per_1k_tokens', '0.001');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.output_per_1k_tokens', '0.003');

        $template = AiPromptTemplate::query()->create([
            'name' => 'Post Title Excerpt Refinement Default',
            'key' => 'post_title_excerpt_refinement_default',
            'type' => AiPromptTemplate::TYPE_EDITORIAL_REFINER,
            'description' => 'Default title and excerpt refinement prompt.',
            'status' => AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $version = AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $template->id,
            'version' => 1,
            'system_prompt' => 'Suggest review-only title and excerpt improvements.',
            'user_prompt' => 'Title {{post_title}} Excerpt {{post_excerpt}} Keyword {{primary_keyword}} Body {{existing_markdown_body}}',
            'output_schema' => ['type' => 'object', 'required' => ['recommended_title', 'recommended_excerpt']],
            'variables' => ['post_title', 'post_excerpt', 'primary_keyword', 'existing_markdown_body'],
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

        return Post::query()->create([
            'author_user_id' => $admin->id,
            'category_id' => $category->id,
            'template_id' => null,
            'featured_media_id' => null,
            'title' => 'AI Draft Post',
            'slug' => 'ai-draft-post',
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
                'markdown_body' => "# AI Draft Post\n\nOriginal body copy.",
            ],
        ]);
    }
}
