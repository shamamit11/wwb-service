<?php

namespace Tests\Feature;

use App\Jobs\AI\GenerateBlogDraftJob;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\Category;
use App\Models\ContentTopic;
use App\Models\User;
use App\Modules\ContentTopics\Data\CreateContentTopicData;
use App\Modules\ContentTopics\Services\CreateContentTopicService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContentTopicAutoAdvanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_advance_approves_score_eighty_five_topics_but_caps_draft_queueing_at_two(): void
    {
        Queue::fake();

        $author = User::factory()->create();
        $category = $this->createCategory($author, 'AI Tools', 'ai-tools');
        $service = app(CreateContentTopicService::class);

        $first = $service->handle($this->topicData((int) $category->id, 'AI Tool Reviews', '85.00'));
        $second = $service->handle($this->topicData((int) $category->id, 'AI Tool Comparisons', '95.00'));
        $third = $service->handle($this->topicData((int) $category->id, 'AI Tool Governance', '97.00'));

        $this->assertSame(ContentTopic::STATUS_APPROVED, $first->status);
        $this->assertSame(ContentTopic::STATUS_APPROVED, $second->status);
        $this->assertSame(ContentTopic::STATUS_APPROVED, $third->status);

        $this->assertSame(2, AiJob::query()->where('type', AiPromptTemplate::TYPE_BLOG_WRITER)->count());
        Queue::assertPushed(GenerateBlogDraftJob::class, 2);
    }

    public function test_it_persists_long_search_intent_text_with_ai_suggested_topics(): void
    {
        Queue::fake();

        $author = User::factory()->create();
        $category = $this->createCategory($author, 'AI Tools', 'ai-tools');
        $service = app(CreateContentTopicService::class);
        $searchIntent = 'Find actionable criteria, benchmarks, and trade-offs for selecting an LLM provider that meets strict latency and concurrency SLAs.';

        $topic = $service->handle(new CreateContentTopicData(
            categoryId: (int) $category->id,
            title: 'How to Choose an LLM Provider for Low-Latency, High-Concurrency Production Apps',
            slug: null,
            cluster: ContentTopic::CLUSTER_AI_TOOLS,
            primaryKeyword: 'llm provider latency',
            secondaryKeywords: ['low latency llm api', 'llm concurrency'],
            searchIntent: $searchIntent,
            priorityScore: '84.00',
            source: ContentTopic::SOURCE_AI_SUGGESTED,
            status: ContentTopic::STATUS_SUGGESTED,
        ));

        $this->assertSame($searchIntent, $topic->search_intent);
        $this->assertDatabaseHas('content_topics', [
            'id' => $topic->id,
            'search_intent' => $searchIntent,
        ]);
        Queue::assertNothingPushed();
    }

    private function topicData(int $categoryId, string $title, string $priorityScore): CreateContentTopicData
    {
        return new CreateContentTopicData(
            categoryId: $categoryId,
            title: $title,
            slug: null,
            cluster: ContentTopic::CLUSTER_AI_TOOLS,
            primaryKeyword: strtolower($title),
            priorityScore: $priorityScore,
            source: ContentTopic::SOURCE_AI_SUGGESTED,
            status: ContentTopic::STATUS_SUGGESTED,
        );
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
