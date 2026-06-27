<?php

namespace Tests\Feature;

use App\Infrastructure\Ai\Contracts\AiClient;
use App\Infrastructure\Ai\Data\AiUsageData;
use App\Infrastructure\Ai\Data\GenerateTextRequest;
use App\Infrastructure\Ai\Data\TextGenerationResult;
use App\Models\Category;
use App\Models\ContentTopic;
use App\Models\User;
use App\Modules\Ai\Data\DiscoverContentTopicsData;
use App\Modules\Ai\Services\TopicDiscoveryWorkflow;
use Database\Seeders\AiPromptTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use stdClass;
use Tests\TestCase;

class TopicDiscoveryWorkflowDailyLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_keeps_high_priority_topics_visible_even_after_daily_auto_queue_limit_is_hit(): void
    {
        Queue::fake();
        $this->seed(AiPromptTemplateSeeder::class);

        $author = User::factory()->create();
        $category = $this->createCategory($author, 'AI Tools', 'ai-tools');

        $this->app->bind(AiClient::class, fn (): AiClient => new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                return new TextGenerationResult(
                    content: json_encode([
                        'topics' => [
                            [
                                'title' => 'Best AI Tools for Content Research',
                                'slug' => 'best-ai-tools-for-content-research',
                                'primary_keyword' => 'ai tools for content research',
                                'priority_score' => 94,
                            ],
                            [
                                'title' => 'How to Compare AI Writing Tools',
                                'slug' => 'how-to-compare-ai-writing-tools',
                                'primary_keyword' => 'compare ai writing tools',
                                'priority_score' => 92,
                            ],
                            [
                                'title' => 'AI Tool Governance for Small Teams',
                                'slug' => 'ai-tool-governance-for-small-teams',
                                'primary_keyword' => 'ai tool governance',
                                'priority_score' => 85,
                            ],
                        ],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'fake',
                    model: 'fake-model',
                    usage: new AiUsageData(promptTokens: 10, completionTokens: 10),
                );
            }
        });

        $result = app(TopicDiscoveryWorkflow::class)->run(new DiscoverContentTopicsData(
            categoryId: (int) $category->id,
            count: 3,
            audience: 'Technical publishers',
            metadata: ['trigger' => 'test'],
        ));

        $this->assertTrue($result->isSuccessful());
        $this->assertCount(3, $result->metadata['saved_topic_ids']);
        $this->assertCount(1, $result->metadata['skipped_daily_limit']);
        $this->assertSame('AI Tool Governance for Small Teams', $result->metadata['skipped_daily_limit'][0]['title']);
        $this->assertDatabaseCount('content_topics', 3);
        $this->assertSame(3, ContentTopic::query()->count());
        $this->assertDatabaseHas('content_topics', [
            'title' => 'AI Tool Governance for Small Teams',
            'status' => ContentTopic::STATUS_APPROVED,
        ]);
    }

    public function test_workflow_accepts_nested_score_breakdown_payloads(): void
    {
        Queue::fake();
        $this->seed(AiPromptTemplateSeeder::class);

        $author = User::factory()->create();
        $category = $this->createCategory($author, 'SEO', 'seo');

        $this->app->bind(AiClient::class, fn (): AiClient => new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                return new TextGenerationResult(
                    content: json_encode([
                        'topics' => [[
                            'title' => 'Schema Patterns That Pass Rich Result Tests',
                            'slug' => 'schema-patterns-rich-result-tests',
                            'primary_keyword' => 'schema rich result tests',
                            'score_breakdown' => [
                                'trend_score' => 34,
                                'knowledge_base_fit' => 18,
                                'business_value' => 19,
                                'originality_gap' => 11,
                                'execution_confidence' => 8,
                            ],
                        ]],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'fake',
                    model: 'fake-model',
                    usage: new AiUsageData(promptTokens: 10, completionTokens: 10),
                );
            }
        });

        $result = app(TopicDiscoveryWorkflow::class)->run(new DiscoverContentTopicsData(
            categoryId: (int) $category->id,
            count: 1,
            audience: 'Technical bloggers and SEO-focused content teams.',
            metadata: ['trigger' => 'test'],
        ));

        $this->assertTrue($result->isSuccessful());
        $this->assertSame([], $result->metadata['skipped_unscored'] ?? []);
        $this->assertDatabaseHas('content_topics', [
            'title' => 'Schema Patterns That Pass Rich Result Tests',
            'priority_score' => '90.00',
            'status' => ContentTopic::STATUS_APPROVED,
        ]);
    }

    public function test_workflow_retains_unscored_topics_for_review(): void
    {
        Queue::fake();
        $this->seed(AiPromptTemplateSeeder::class);

        $author = User::factory()->create();
        $category = $this->createCategory($author, 'SEO', 'seo');

        $this->app->bind(AiClient::class, fn (): AiClient => new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                return new TextGenerationResult(
                    content: json_encode([
                        'topics' => [[
                            'title' => 'Crawl Budget Audits for Large Sites',
                            'slug' => 'crawl-budget-audits-large-sites',
                            'primary_keyword' => 'crawl budget audit',
                        ]],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'fake',
                    model: 'fake-model',
                    usage: new AiUsageData(promptTokens: 10, completionTokens: 10),
                );
            }
        });

        $result = app(TopicDiscoveryWorkflow::class)->run(new DiscoverContentTopicsData(
            categoryId: (int) $category->id,
            count: 1,
            audience: 'Technical bloggers and SEO-focused content teams.',
            metadata: ['trigger' => 'test'],
        ));

        $this->assertTrue($result->isSuccessful());
        $this->assertCount(1, $result->metadata['skipped_unscored'] ?? []);
        $this->assertSame('Crawl Budget Audits for Large Sites', $result->metadata['skipped_unscored'][0]['title']);
        $this->assertTrue($result->metadata['skipped_unscored'][0]['persisted']);
        $this->assertDatabaseHas('content_topics', [
            'title' => 'Crawl Budget Audits for Large Sites',
        ]);
    }

    public function test_workflow_persists_duplicate_discoveries_for_editor_visibility(): void
    {
        Queue::fake();
        $this->seed(AiPromptTemplateSeeder::class);

        $author = User::factory()->create();
        $category = $this->createCategory($author, 'AI Tools', 'ai-tools');

        ContentTopic::query()->create([
            'category_id' => $category->id,
            'title' => 'AI Tool Governance for Small Teams',
            'slug' => 'ai-tool-governance-for-small-teams-existing',
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'primary_keyword' => 'ai tool governance',
            'priority_score' => '88.00',
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_SUGGESTED,
        ]);

        $this->app->bind(AiClient::class, fn (): AiClient => new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                return new TextGenerationResult(
                    content: json_encode([
                        'topics' => [[
                            'title' => 'AI Tool Governance for Small Teams',
                            'slug' => 'ai-tool-governance-for-small-teams',
                            'primary_keyword' => 'ai tool governance',
                            'priority_score' => 92,
                        ]],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'fake',
                    model: 'fake-model',
                    usage: new AiUsageData(promptTokens: 10, completionTokens: 10),
                );
            }
        });

        $result = app(TopicDiscoveryWorkflow::class)->run(new DiscoverContentTopicsData(
            categoryId: (int) $category->id,
            count: 1,
            audience: 'Technical publishers',
            metadata: ['trigger' => 'test'],
        ));

        $this->assertTrue($result->isSuccessful());
        $this->assertCount(1, $result->metadata['skipped_duplicates'] ?? []);
        $this->assertTrue($result->metadata['skipped_duplicates'][0]['persisted']);
        $this->assertSame(2, ContentTopic::query()->count());

        $duplicateTopic = ContentTopic::query()
            ->where('slug', 'ai-tool-governance-for-small-teams')
            ->firstOrFail();

        $this->assertSame(ContentTopic::RECOMMENDATION_DUPLICATE, $duplicateTopic->editorialRecommendation());
        $this->assertTrue($duplicateTopic->isDuplicateDiscovery());
        $this->assertSame(['content_topic'], $duplicateTopic->duplicateMatches());
    }

    public function test_workflow_includes_category_brief_in_prompt_for_ai_tools(): void
    {
        Queue::fake();
        $this->seed(AiPromptTemplateSeeder::class);

        $author = User::factory()->create();
        $category = $this->createCategory($author, 'AI Tools', 'ai-tools');
        $capture = (object) ['request' => null];

        $this->app->bind(AiClient::class, fn (): AiClient => new class($capture) implements AiClient
        {
            public function __construct(private stdClass $capture) {}

            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                $this->capture->request = $request;

                return new TextGenerationResult(
                    content: json_encode([
                        'topics' => [[
                            'title' => 'Best AI Coding Assistants for PHP Teams',
                            'slug' => 'best-ai-coding-assistants-for-php-teams',
                            'primary_keyword' => 'ai coding assistants for php teams',
                            'priority_score' => 91,
                        ]],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'fake',
                    model: 'fake-model',
                    usage: new AiUsageData(promptTokens: 10, completionTokens: 10),
                );
            }
        });

        $result = app(TopicDiscoveryWorkflow::class)->run(new DiscoverContentTopicsData(
            categoryId: (int) $category->id,
            count: 1,
            audience: 'Technical publishers',
            metadata: ['trigger' => 'test'],
        ));

        $this->assertTrue($result->isSuccessful());
        $this->assertInstanceOf(GenerateTextRequest::class, $capture->request);
        $this->assertStringContainsString('Category brief Focus on practical, tool-specific, commercially relevant AI content.', $capture->request->prompt);
        $this->assertStringContainsString('Favor named tools, tool categories, comparisons, reviews, alternatives, best-tools-for-X', $capture->request->prompt);
    }

    public function test_non_ai_tools_categories_still_work_without_category_brief_guidance(): void
    {
        Queue::fake();
        $this->seed(AiPromptTemplateSeeder::class);

        $author = User::factory()->create();
        $category = $this->createCategory($author, 'SEO', 'seo');
        $capture = (object) ['request' => null];

        $this->app->bind(AiClient::class, fn (): AiClient => new class($capture) implements AiClient
        {
            public function __construct(private stdClass $capture) {}

            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                $this->capture->request = $request;

                return new TextGenerationResult(
                    content: json_encode([
                        'topics' => [[
                            'title' => 'SEO Reporting Workflows for Small Teams',
                            'slug' => 'seo-reporting-workflows-for-small-teams',
                            'primary_keyword' => 'seo reporting workflows',
                            'priority_score' => 82,
                        ]],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'fake',
                    model: 'fake-model',
                    usage: new AiUsageData(promptTokens: 10, completionTokens: 10),
                );
            }
        });

        $result = app(TopicDiscoveryWorkflow::class)->run(new DiscoverContentTopicsData(
            categoryId: (int) $category->id,
            count: 1,
            audience: 'SEO leads',
            metadata: ['trigger' => 'test'],
        ));

        $this->assertTrue($result->isSuccessful());
        $this->assertInstanceOf(GenerateTextRequest::class, $capture->request);
        $this->assertStringContainsString('Category brief ', $capture->request->prompt);
        $this->assertStringNotContainsString('Focus on practical, tool-specific, commercially relevant AI content.', $capture->request->prompt);
        $this->assertDatabaseHas('content_topics', [
            'title' => 'SEO Reporting Workflows for Small Teams',
            'status' => ContentTopic::STATUS_SUGGESTED,
        ]);
    }

    private function createCategory(User $author, string $name, string $slug): Category
    {
        return Category::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'name' => $name,
            'slug' => $slug,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }
}
