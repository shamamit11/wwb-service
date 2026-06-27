<?php

namespace Tests\Feature;

use App\Jobs\AI\GenerateBlogDraftJob;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\Category;
use App\Models\ContentTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContentTopicApproveApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_topic_without_queueing_draft_by_default(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Tools', 'ai-tools');
        $topic = $this->createSuggestedTopic($category, 'AI Tool Reviews');

        $this->withToken($token)
            ->postJson("/api/v1/admin/content-topics/{$topic->id}/approve", [
                'notes' => 'Looks good for the queue.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', ContentTopic::STATUS_APPROVED)
            ->assertJsonPath('data.notes', 'Looks good for the queue.');

        $this->assertDatabaseHas('content_topics', [
            'id' => $topic->id,
            'status' => ContentTopic::STATUS_APPROVED,
        ]);
        $this->assertSame(0, AiJob::query()->where('type', AiPromptTemplate::TYPE_BLOG_WRITER)->count());
        Queue::assertNothingPushed();
    }

    public function test_admin_can_approve_topic_and_queue_draft_in_one_action(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Tools', 'ai-tools');
        $topic = $this->createSuggestedTopic($category, 'AI Tool Comparisons');

        $this->withToken($token)
            ->postJson("/api/v1/admin/content-topics/{$topic->id}/approve", [
                'notes' => 'Approve and draft this now.',
                'queue_draft' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', ContentTopic::STATUS_APPROVED)
            ->assertJsonPath('data.notes', 'Approve and draft this now.');

        $this->assertDatabaseHas('content_topics', [
            'id' => $topic->id,
            'status' => ContentTopic::STATUS_APPROVED,
        ]);
        $this->assertSame(1, AiJob::query()->where('type', AiPromptTemplate::TYPE_BLOG_WRITER)->count());
        Queue::assertPushed(GenerateBlogDraftJob::class, 1);
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

    private function createSuggestedTopic(Category $category, string $title): ContentTopic
    {
        return ContentTopic::query()->create([
            'category_id' => $category->id,
            'title' => $title,
            'slug' => str($title)->slug()->value(),
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'primary_keyword' => strtolower($title),
            'priority_score' => '88.00',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_SUGGESTED,
        ]);
    }
}
