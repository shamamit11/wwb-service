<?php

namespace Tests\Feature;

use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\Category;
use App\Models\ContentTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTopicVisibilityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_topics_by_recommendation_duplicate_flag_and_draft_job_visibility(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Tools', 'ai-tools');

        $autoQueued = ContentTopic::query()->create([
            'category_id' => $category->id,
            'title' => 'Best AI Tools for Content Research',
            'slug' => 'best-ai-tools-for-content-research',
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'primary_keyword' => 'ai tools for content research',
            'priority_score' => '94.00',
            'score_breakdown' => [
                'trend_score' => 34,
                'knowledge_base_fit' => 18,
                'business_value' => 18,
                'originality_gap' => 14,
                'execution_confidence' => 10,
            ],
            'discovery_metadata' => [
                'recommendation' => ContentTopic::RECOMMENDATION_AUTO_QUEUE,
                'summary' => 'High-conviction topic for immediate drafting.',
            ],
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        ContentTopic::query()->create([
            'category_id' => $category->id,
            'title' => 'AI Tool Governance for Small Teams',
            'slug' => 'ai-tool-governance-for-small-teams',
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'primary_keyword' => 'ai tool governance',
            'priority_score' => '62.00',
            'discovery_metadata' => [
                'recommendation' => ContentTopic::RECOMMENDATION_LOW_SCORE,
                'weaknesses' => ['Low urgency compared with current backlog.'],
            ],
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_SUGGESTED,
        ]);

        ContentTopic::query()->create([
            'category_id' => $category->id,
            'title' => 'How to Compare AI Writing Tools',
            'slug' => 'how-to-compare-ai-writing-tools',
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'primary_keyword' => 'compare ai writing tools',
            'priority_score' => '91.00',
            'discovery_metadata' => [
                'recommendation' => ContentTopic::RECOMMENDATION_DUPLICATE,
                'is_duplicate' => true,
                'duplicate_matches' => ['post'],
            ],
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_SUGGESTED,
        ]);

        AiJob::query()->create([
            'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
            'status' => AiJob::STATUS_QUEUED,
            'entity_type' => 'content_topic',
            'entity_id' => $autoQueued->id,
            'input_payload' => ['content_topic_id' => $autoQueued->id],
            'attempts' => 1,
            'retry_of_ai_job_id' => null,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/content-topics?recommendation=duplicate&is_duplicate=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'How to Compare AI Writing Tools')
            ->assertJsonPath('data.0.editorial_recommendation', ContentTopic::RECOMMENDATION_DUPLICATE)
            ->assertJsonPath('data.0.is_duplicate', true)
            ->assertJsonPath('data.0.duplicate_matches.0', 'post');

        $this->withToken($token)
            ->getJson('/api/v1/admin/content-topics?recommendation=low_score')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'AI Tool Governance for Small Teams')
            ->assertJsonPath('data.0.editorial_recommendation', ContentTopic::RECOMMENDATION_LOW_SCORE);

        $this->withToken($token)
            ->getJson('/api/v1/admin/content-topics?has_draft_generation_job=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $autoQueued->id)
            ->assertJsonPath('data.0.has_draft_generation_job', true)
            ->assertJsonPath('data.0.editorial_recommendation', ContentTopic::RECOMMENDATION_AUTO_QUEUE);
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
