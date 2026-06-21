<?php

namespace Tests\Feature;

use App\Jobs\AI\GenerateBlogDraftJob;
use App\Models\Category;
use App\Models\ContentTopic;
use App\Models\KnowledgeBaseEntry;
use App\Models\NewsItem;
use App\Models\User;
use App\Modules\News\Services\NewsRoutingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NewsRoutingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_routing_service_creates_knowledge_base_and_topic_for_high_confidence_news(): void
    {
        Queue::fake();

        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Tools', 'ai-tools');

        $item = NewsItem::query()->create([
            'external_id' => 'news-1',
            'provider' => 'currents',
            'source_id' => null,
            'category_id' => $category->id,
            'publisher_name' => 'TechCrunch',
            'title' => 'AI Tool Release Guide for Workflow Automation',
            'normalized_title' => 'ai tool release guide for workflow automation',
            'url' => 'https://example.com/ai-tool-release',
            'canonical_url' => 'https://example.com/ai-tool-release',
            'description' => 'A detailed release and integration guide for a new AI workflow tool.',
            'author' => 'Reporter',
            'language' => 'en',
            'country' => 'us',
            'published_at' => now()->subHour(),
            'discovered_at' => now(),
            'status' => NewsItem::STATUS_DISCOVERED,
        ]);

        $item->scores()->create([
            'relevance_score' => 24,
            'freshness_score' => 15,
            'credibility_score' => 18,
            'pillar_fit_score' => 14,
            'evergreen_potential_score' => 10,
            'novelty_score' => 10,
            'business_value_score' => 9,
            'total_score' => 90,
            'decision' => 'knowledge_base_and_topic',
            'reasoning' => 'test',
            'scored_at' => now(),
        ]);

        $item->extractions()->create([
            'extractor' => 'firecrawl',
            'content_markdown' => "# AI Tool Release Guide\n\nNew workflow details.",
            'content_text' => 'AI Tool Release Guide New workflow details.',
            'excerpt' => 'New workflow details.',
            'facts_json' => ['items' => ['launch announced']],
            'entities_json' => ['publisher' => 'TechCrunch'],
            'claims_json' => ['items' => ['The release targets workflow automation.']],
            'extracted_at' => now(),
        ]);

        $route = app(NewsRoutingService::class)->handle($item);

        $knowledgeBase = KnowledgeBaseEntry::query()->find($route->knowledge_base_entry_id);
        $topic = ContentTopic::query()->find($route->content_topic_id);

        $this->assertNotNull($knowledgeBase);
        $this->assertNotNull($topic);
        $this->assertSame(ContentTopic::SOURCE_NEWS_SIGNAL, $topic->source);
        $this->assertSame(ContentTopic::STATUS_APPROVED, $topic->status);
        $this->assertContains(['id' => $topic->id], $knowledgeBase->linkedTopics());
        Queue::assertPushed(GenerateBlogDraftJob::class, 1);
        $this->assertDatabaseHas('news_item_routes', [
            'news_item_id' => $item->id,
            'route' => 'knowledge_base_and_topic',
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
