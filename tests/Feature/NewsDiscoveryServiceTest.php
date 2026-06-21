<?php

namespace Tests\Feature;

use App\Infrastructure\News\Contracts\NewsDiscoveryClient;
use App\Infrastructure\News\Data\DiscoveredNewsArticleData;
use App\Models\Category;
use App\Models\User;
use App\Modules\News\Services\NewsDiscoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NewsDiscoveryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_tables_exist_and_discovery_persists_results(): void
    {
        $this->assertTrue(Schema::hasTable('news_sources'));
        $this->assertTrue(Schema::hasTable('news_items'));
        $this->assertTrue(Schema::hasTable('news_item_extractions'));
        $this->assertTrue(Schema::hasTable('news_item_scores'));
        $this->assertTrue(Schema::hasTable('news_item_routes'));

        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Tools', 'ai-tools');

        $this->app->instance(NewsDiscoveryClient::class, new class implements NewsDiscoveryClient
        {
            public function search(Category $category, string $query, int $limit): array
            {
                return [
                    new DiscoveredNewsArticleData(
                        externalId: 'currents-1',
                        provider: 'currents',
                        publisherName: 'TechCrunch',
                        publisherDomain: 'techcrunch.com',
                        title: 'New AI Tool Launch for Editorial Teams',
                        url: 'https://techcrunch.com/example-ai-tool',
                        canonicalUrl: 'https://techcrunch.com/example-ai-tool',
                        description: 'A new AI tool targets editorial workflow automation.',
                        author: 'TC Reporter',
                        language: 'en',
                        country: 'us',
                        publishedAt: now()->subHour()->toIso8601String(),
                        metadata: ['query' => $query],
                    ),
                ];
            }
        });

        $items = app(NewsDiscoveryService::class)->handle($category, 1, ['trigger' => 'test']);

        $this->assertCount(1, $items);
        $this->assertDatabaseHas('news_sources', [
            'name' => 'TechCrunch',
            'slug' => 'techcrunch',
        ]);
        $this->assertDatabaseHas('news_items', [
            'provider' => 'currents',
            'external_id' => 'currents-1',
            'category_id' => $category->id,
            'publisher_name' => 'TechCrunch',
            'status' => 'discovered',
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
