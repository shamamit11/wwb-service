<?php

namespace Tests\Feature;

use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\Category;
use App\Models\ContentTopic;
use App\Models\User;
use App\Modules\Ai\Services\AiAutomationDailyLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAutomationDailyLimitServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_treats_score_of_eighty_five_as_high_priority(): void
    {
        $service = app(AiAutomationDailyLimitService::class);

        $this->assertTrue($service->isHighPriorityScore('85.00'));
        $this->assertTrue($service->isHighPriorityScore('91'));
        $this->assertFalse($service->isHighPriorityScore('84.99'));
    }

    public function test_service_caps_daily_high_priority_topics_and_draft_jobs(): void
    {
        $author = User::factory()->create();
        $category = $this->createCategory($author, 'AI Tools', 'ai-tools');

        ContentTopic::query()->create([
            'category_id' => $category->id,
            'title' => 'AI Tooling Systems',
            'slug' => 'ai-tooling-systems',
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'priority_score' => '85.00',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_SUGGESTED,
        ]);

        ContentTopic::query()->create([
            'category_id' => $category->id,
            'title' => 'AI Tooling Workflows',
            'slug' => 'ai-tooling-workflows',
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'priority_score' => '95.00',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_SUGGESTED,
        ]);

        AiJob::query()->create([
            'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
            'status' => AiJob::STATUS_QUEUED,
            'entity_type' => 'content_topic',
            'entity_id' => 1,
            'input_payload' => ['content_topic_id' => 1],
            'attempts' => 1,
            'retry_of_ai_job_id' => null,
        ]);

        AiJob::query()->create([
            'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
            'status' => AiJob::STATUS_QUEUED,
            'entity_type' => 'content_topic',
            'entity_id' => 2,
            'input_payload' => ['content_topic_id' => 2],
            'attempts' => 1,
            'retry_of_ai_job_id' => null,
        ]);

        $service = app(AiAutomationDailyLimitService::class);

        $this->assertFalse($service->canPersistAiSuggestedTopic('92.00'));
        $this->assertTrue($service->canPersistAiSuggestedTopic('84.00'));
        $this->assertFalse($service->canQueueAutomaticDraft());
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
