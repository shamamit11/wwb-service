<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ContentTopic;
use App\Models\User;
use App\Modules\ContentTopics\Services\DeleteLowScoreTopicsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteLowScoreTopicsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_retains_low_score_topics_by_default(): void
    {
        $author = User::factory()->create();
        $category = $this->createCategory($author, 'SEO', 'seo');

        $topic = ContentTopic::query()->create([
            'category_id' => $category->id,
            'title' => 'Legacy Directory Submission Tactics',
            'slug' => 'legacy-directory-submission-tactics',
            'cluster' => ContentTopic::CLUSTER_SEO,
            'priority_score' => '42.00',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_SUGGESTED,
        ]);

        $deleted = app(DeleteLowScoreTopicsService::class)->handle();

        $this->assertSame(0, $deleted);
        $this->assertDatabaseHas('content_topics', ['id' => $topic->id]);
    }

    public function test_service_can_still_hard_delete_low_score_topics_when_requested(): void
    {
        $author = User::factory()->create();
        $category = $this->createCategory($author, 'SEO', 'seo');

        $topic = ContentTopic::query()->create([
            'category_id' => $category->id,
            'title' => 'Deprecated SEO Tactics',
            'slug' => 'deprecated-seo-tactics',
            'cluster' => ContentTopic::CLUSTER_SEO,
            'priority_score' => '30.00',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_SUGGESTED,
        ]);

        $deleted = app(DeleteLowScoreTopicsService::class)->handle(true);

        $this->assertSame(1, $deleted);
        $this->assertDatabaseMissing('content_topics', ['id' => $topic->id]);
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
