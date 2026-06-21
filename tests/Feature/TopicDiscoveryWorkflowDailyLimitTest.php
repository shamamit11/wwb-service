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
use Tests\TestCase;

class TopicDiscoveryWorkflowDailyLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_saves_only_two_high_priority_topics_per_day(): void
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
                                'priority_score' => 90,
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
        $this->assertCount(2, $result->metadata['saved_topic_ids']);
        $this->assertCount(1, $result->metadata['skipped_daily_limit']);
        $this->assertSame('AI Tool Governance for Small Teams', $result->metadata['skipped_daily_limit'][0]['title']);
        $this->assertDatabaseCount('content_topics', 2);
        $this->assertSame(2, ContentTopic::query()->count());
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
