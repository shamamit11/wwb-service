<?php

namespace Tests\Feature;

use App\Infrastructure\News\Contracts\NewsContentExtractionClient;
use App\Infrastructure\News\Contracts\NewsDiscoveryClient;
use App\Infrastructure\News\Data\DiscoveredNewsArticleData;
use App\Infrastructure\News\Data\ExtractedNewsContentData;
use App\Models\Category;
use App\Models\NewsItem;
use App\Models\NewsItemExtraction;
use App\Models\NewsItemRoute;
use App\Models\NewsItemScore;
use App\Models\NewsSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NewsAdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_news_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/news-items')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_list_show_and_trigger_news_pipeline_actions(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Tools', 'ai-tools');
        $source = NewsSource::query()->create([
            'name' => 'OpenAI News',
            'slug' => 'openai-news',
            'kind' => NewsSource::KIND_PUBLISHER,
            'base_url' => 'https://openai.com',
            'trust_score' => 18,
            'is_active' => true,
            'metadata' => null,
        ]);

        $newsItem = NewsItem::query()->create([
            'external_id' => 'article-1',
            'provider' => 'currents',
            'source_id' => $source->id,
            'category_id' => $category->id,
            'publisher_name' => 'OpenAI News',
            'title' => 'Laravel MCP automation update',
            'normalized_title' => 'laravel mcp automation update',
            'url' => 'https://example.com/news/laravel-mcp',
            'canonical_url' => 'https://example.com/news/laravel-mcp',
            'description' => 'Fresh update about Laravel, MCP, and automation.',
            'author' => 'Reporter',
            'language' => 'en',
            'country' => 'us',
            'published_at' => now()->subHours(3),
            'discovered_at' => now(),
            'status' => NewsItem::STATUS_DISCOVERED,
            'metadata' => ['seeded' => true],
        ]);

        NewsItemScore::query()->create([
            'news_item_id' => $newsItem->id,
            'relevance_score' => 24,
            'freshness_score' => 15,
            'credibility_score' => 18,
            'pillar_fit_score' => 8,
            'evergreen_potential_score' => 6,
            'novelty_score' => 10,
            'business_value_score' => 8,
            'total_score' => 89,
            'decision' => NewsItemScore::DECISION_KNOWLEDGE_BASE_AND_TOPIC,
            'reasoning' => 'seeded score',
            'scored_at' => now(),
        ]);

        NewsItemExtraction::query()->create([
            'news_item_id' => $newsItem->id,
            'extractor' => 'firecrawl',
            'content_markdown' => '# Headline',
            'content_text' => 'Headline facts and details.',
            'excerpt' => 'Headline facts',
            'facts_json' => ['facts' => ['f1']],
            'entities_json' => ['product' => 'MCP'],
            'claims_json' => ['items' => ['Claim 1']],
            'extracted_at' => now(),
            'metadata' => ['seeded' => true],
        ]);

        NewsItemRoute::query()->create([
            'news_item_id' => $newsItem->id,
            'route' => NewsItemRoute::ROUTE_KNOWLEDGE_BASE_AND_TOPIC,
            'knowledge_base_entry_id' => null,
            'content_topic_id' => null,
            'post_id' => null,
            'routed_at' => now(),
            'metadata' => ['seeded' => true],
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/news-items?search=Laravel&decision=knowledge_base_and_topic')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $newsItem->id)
            ->assertJsonPath('data.0.latest_score.total_score', 89)
            ->assertJsonPath('data.0.latest_route.route', NewsItemRoute::ROUTE_KNOWLEDGE_BASE_AND_TOPIC);

        $this->withToken($token)
            ->getJson("/api/v1/admin/news-items/{$newsItem->id}")
            ->assertOk()
            ->assertJsonPath('data.source.slug', 'openai-news')
            ->assertJsonPath('data.category.slug', 'ai-tools');

        config()->set('news.discovery.category_queries.ai-tools', ['laravel mcp']);

        app()->bind(NewsDiscoveryClient::class, fn () => new class implements NewsDiscoveryClient
        {
            public function search(Category $category, string $query, int $limit): array
            {
                return [
                    new DiscoveredNewsArticleData(
                        externalId: 'article-2',
                        provider: 'currents',
                        publisherName: 'Example Source',
                        publisherDomain: 'example.com',
                        title: 'Laravel MCP automation API workflow for developers',
                        url: 'https://example.com/news/ai-tools-workflow',
                        canonicalUrl: 'https://example.com/news/ai-tools-workflow',
                        description: 'AI tools update for Laravel automation and developer APIs.',
                        author: 'Desk',
                        language: 'en',
                        country: 'us',
                        publishedAt: now()->toIso8601String(),
                        metadata: ['query' => $query],
                    ),
                ];
            }
        });

        $this->withToken($token)
            ->postJson('/api/v1/admin/news-items/discover', [
                'category_id' => $category->id,
                'limit' => 1,
                'sync' => true,
            ])
            ->assertCreated()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Laravel MCP automation API workflow for developers');

        $scoreTarget = NewsItem::query()->where('external_id', 'article-2')->firstOrFail();

        $this->withToken($token)
            ->postJson("/api/v1/admin/news-items/{$scoreTarget->id}/score")
            ->assertAccepted()
            ->assertJsonPath('data.id', $scoreTarget->id)
            ->assertJsonPath('data.latest_score.decision', NewsItemScore::DECISION_KNOWLEDGE_BASE_AND_TOPIC);

        app()->bind(NewsContentExtractionClient::class, fn () => new class implements NewsContentExtractionClient
        {
            public function extract(string $url): ExtractedNewsContentData
            {
                return new ExtractedNewsContentData(
                    contentMarkdown: '# Example',
                    contentText: 'Example extracted article text.',
                    excerpt: 'Example extracted article text.',
                    facts: ['url' => $url],
                    entities: ['company' => 'Example'],
                    claims: ['Example claim'],
                    metadata: ['source' => 'fake'],
                );
            }
        });

        $this->withToken($token)
            ->postJson("/api/v1/admin/news-items/{$scoreTarget->id}/extract")
            ->assertAccepted()
            ->assertJsonPath('data.latest_extraction.extractor', 'firecrawl')
            ->assertJsonPath('data.latest_extraction.entities_json.company', 'Example');

        $this->withToken($token)
            ->postJson("/api/v1/admin/news-items/{$scoreTarget->id}/route")
            ->assertAccepted()
            ->assertJsonPath('data.status', NewsItem::STATUS_ROUTED)
            ->assertJsonPath('data.latest_route.route', NewsItemRoute::ROUTE_KNOWLEDGE_BASE_AND_TOPIC);
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
            'sort_order' => 0,
        ]);
    }
}
