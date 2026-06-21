<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\NewsItem;
use App\Models\NewsItemScore;
use App\Models\User;
use App\Modules\News\Services\NewsScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_scoring_service_classifies_strong_developer_ai_news_as_knowledge_base_and_topic(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'Developer AI', 'developer-ai');
        $item = NewsItem::query()->create([
            'external_id' => 'dev-ai-1',
            'provider' => 'currents',
            'source_id' => null,
            'category_id' => $category->id,
            'publisher_name' => 'OpenAI',
            'title' => 'Laravel API Release Guide for MCP Agent Workflows',
            'normalized_title' => 'laravel api release guide for mcp agent workflows',
            'url' => 'https://example.com/laravel-mcp-guide',
            'canonical_url' => 'https://example.com/laravel-mcp-guide',
            'description' => 'A developer guide covering Laravel, MCP, API integration, and agent workflow design.',
            'author' => 'Reporter',
            'language' => 'en',
            'country' => 'us',
            'published_at' => now()->subHours(2),
            'discovered_at' => now(),
            'status' => NewsItem::STATUS_DISCOVERED,
        ]);

        $score = app(NewsScoringService::class)->handle($item);

        $this->assertSame(NewsItemScore::DECISION_KNOWLEDGE_BASE_AND_TOPIC, $score->decision);
        $this->assertGreaterThanOrEqual(80, $score->total_score);
        $this->assertDatabaseHas('news_item_scores', [
            'news_item_id' => $item->id,
            'decision' => NewsItemScore::DECISION_KNOWLEDGE_BASE_AND_TOPIC,
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
