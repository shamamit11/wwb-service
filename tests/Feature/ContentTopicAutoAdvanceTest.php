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

    public function test_auto_advance_approves_score_ninety_topics_but_caps_draft_queueing_at_two(): void
    {
        Queue::fake();

        $author = User::factory()->create();
        $category = $this->createCategory($author, 'AI Tools', 'ai-tools');
        $service = app(CreateContentTopicService::class);

        $first = $service->handle($this->topicData((int) $category->id, 'AI Tool Reviews', '90.00'));
        $second = $service->handle($this->topicData((int) $category->id, 'AI Tool Comparisons', '95.00'));
        $third = $service->handle($this->topicData((int) $category->id, 'AI Tool Governance', '97.00'));

        $this->assertSame(ContentTopic::STATUS_APPROVED, $first->status);
        $this->assertSame(ContentTopic::STATUS_APPROVED, $second->status);
        $this->assertSame(ContentTopic::STATUS_APPROVED, $third->status);

        $this->assertSame(2, AiJob::query()->where('type', AiPromptTemplate::TYPE_BLOG_WRITER)->count());
        Queue::assertPushed(GenerateBlogDraftJob::class, 2);
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
