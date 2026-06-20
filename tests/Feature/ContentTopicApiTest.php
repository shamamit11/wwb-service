<?php

namespace Tests\Feature;

use App\Jobs\AI\GenerateContentBriefJob;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContentTopicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_content_topic_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/content-topics')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_crud_filter_and_transition_content_topics(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/v1/admin/content-topics', [
            'title' => 'Best AI SEO Tools for Technical Blogs',
            'cluster' => ContentTopic::CLUSTER_SEO,
            'primary_keyword' => 'ai seo tools',
            'secondary_keywords' => ['technical blog seo', 'seo workflow'],
            'search_intent' => 'commercial',
            'priority_score' => '87.50',
            'difficulty_note' => 'Moderate competition with strong SaaS SERPs.',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'notes' => 'Suggested from weekly SERP review.',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.title', 'Best AI SEO Tools for Technical Blogs')
            ->assertJsonPath('data.slug', 'best-ai-seo-tools-for-technical-blogs')
            ->assertJsonPath('data.status', ContentTopic::STATUS_SUGGESTED)
            ->assertJsonPath('data.source', ContentTopic::SOURCE_AI_SUGGESTED)
            ->assertJsonPath('data.can_generate_content_brief', false)
            ->assertJsonPath('data.secondary_keywords.1', 'seo workflow');

        $topicId = (int) $createResponse->json('data.id');

        ContentTopic::query()->create([
            'title' => 'Agent Memory Patterns',
            'slug' => 'agent-memory-patterns',
            'cluster' => ContentTopic::CLUSTER_DEVELOPER_AI,
            'primary_keyword' => 'agent memory patterns',
            'secondary_keywords' => ['tool use loops'],
            'search_intent' => 'informational',
            'priority_score' => '72.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => 'Seeded by editor.',
            'approved_at' => now(),
        ]);

        $this->withToken($token)->getJson('/api/v1/admin/content-topics?status=suggested&cluster=seo&search=technical&sort=title')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $topicId);

        $this->withToken($token)->getJson("/api/v1/admin/content-topics/{$topicId}")
            ->assertOk()
            ->assertJsonPath('data.primary_keyword', 'ai seo tools')
            ->assertJsonPath('data.cluster', ContentTopic::CLUSTER_SEO);

        $this->withToken($token)->patchJson("/api/v1/admin/content-topics/{$topicId}", [
            'title' => 'Best AI SEO Workflows for Technical Blogs',
            'cluster' => ContentTopic::CLUSTER_SEO,
            'primary_keyword' => 'ai seo workflow',
            'secondary_keywords' => ['technical blog seo', 'content ops'],
            'search_intent' => 'commercial',
            'priority_score' => '91.25',
            'difficulty_note' => 'SERP is crowded but beatable with examples.',
            'source' => ContentTopic::SOURCE_MANUAL,
            'notes' => 'Promoted for Q3.',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Best AI SEO Workflows for Technical Blogs')
            ->assertJsonPath('data.slug', 'best-ai-seo-workflows-for-technical-blogs')
            ->assertJsonPath('data.source', ContentTopic::SOURCE_MANUAL);

        $this->withToken($token)->postJson("/api/v1/admin/content-topics/{$topicId}/approve", [
            'notes' => 'Approved for brief generation.',
        ])->assertOk()
            ->assertJsonPath('data.status', ContentTopic::STATUS_APPROVED)
            ->assertJsonPath('data.can_generate_content_brief', true)
            ->assertJsonPath('data.notes', 'Approved for brief generation.');

        $job = AiJob::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('ai_jobs', [
            'id' => $job->id,
            'type' => AiPromptTemplate::TYPE_CONTENT_BRIEF,
            'status' => AiJob::STATUS_QUEUED,
            'entity_type' => 'content_topic',
            'entity_id' => $topicId,
            'attempts' => 1,
        ]);

        Queue::assertPushed(GenerateContentBriefJob::class, function (GenerateContentBriefJob $queuedJob) use ($job): bool {
            return $queuedJob->aiJobId === (int) $job->id
                && $queuedJob->queue === 'ai';
        });

        $this->withToken($token)->postJson("/api/v1/admin/content-topics/{$topicId}/mark-used", [
            'notes' => 'Brief generated for article pipeline.',
        ])->assertOk()
            ->assertJsonPath('data.status', ContentTopic::STATUS_USED)
            ->assertJsonPath('data.can_generate_content_brief', false)
            ->assertJsonPath('data.notes', 'Brief generated for article pipeline.');

        $this->withToken($token)->deleteJson("/api/v1/admin/content-topics/{$topicId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('content_topics', [
            'id' => $topicId,
        ]);
    }

    public function test_duplicate_topic_creation_is_rejected_by_service_logic(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        ContentTopic::query()->create([
            'title' => 'AI Topic Clustering for Blogs',
            'slug' => 'ai-topic-clustering-for-blogs',
            'cluster' => ContentTopic::CLUSTER_CONTENT_MARKETING,
            'primary_keyword' => 'topic clustering',
            'secondary_keywords' => [],
            'search_intent' => null,
            'priority_score' => null,
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_SUGGESTED,
            'notes' => null,
        ]);

        $this->withToken($token)->postJson('/api/v1/admin/content-topics', [
            'title' => 'AI Topic Clustering for Blogs',
            'cluster' => ContentTopic::CLUSTER_CONTENT_MARKETING,
            'primary_keyword' => 'different keyword',
            'source' => ContentTopic::SOURCE_MANUAL,
        ])->assertStatus(409)
            ->assertJsonPath('error_code', 'CONFLICT')
            ->assertJsonPath('errors.title.0', 'AI Topic Clustering for Blogs')
            ->assertJsonPath('errors.cluster.0', ContentTopic::CLUSTER_CONTENT_MARKETING);
    }

    public function test_only_approved_topics_can_be_marked_used(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'LLM Content Review Checklists',
            'slug' => 'llm-content-review-checklists',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'content review checklist',
            'secondary_keywords' => [],
            'search_intent' => null,
            'priority_score' => null,
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_SUGGESTED,
            'notes' => null,
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/content-topics/{$topic->id}/mark-used")
            ->assertStatus(409)
            ->assertJsonPath('error_code', 'CONFLICT')
            ->assertJsonPath('errors.status.0', ContentTopic::STATUS_SUGGESTED)
            ->assertJsonPath('errors.action.0', 'mark-used');
    }

    public function test_approving_topic_does_not_queue_brief_when_one_already_exists(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'AI Topic With Existing Brief',
            'slug' => 'ai-topic-with-existing-brief',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'existing brief topic',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '70.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_REJECTED,
            'notes' => 'Rejected pending review.',
            'rejected_at' => now(),
        ]);

        ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'AI Topic With Existing Brief',
            'slug' => 'ai-topic-with-existing-brief',
            'meta_title' => null,
            'meta_description' => null,
            'primary_keyword' => 'existing brief topic',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'outline' => [['heading' => 'Intro', 'purpose' => 'Frame the topic']],
            'headings' => ['Intro'],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_DRAFT,
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/content-topics/{$topic->id}/approve", [
            'notes' => 'Re-approved with existing brief.',
        ])->assertOk()
            ->assertJsonPath('data.status', ContentTopic::STATUS_APPROVED);

        $this->assertDatabaseCount('ai_jobs', 0);
        Queue::assertNothingPushed();
    }

    public function test_content_topic_validation_errors_use_consistent_json_shape(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/admin/content-topics', [
            'title' => '',
            'cluster' => 'unknown-cluster',
            'secondary_keywords' => ['ok', 123],
            'priority_score' => 1000,
            'source' => 'spreadsheet',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['title', 'cluster', 'secondary_keywords.1', 'priority_score', 'source'],
                'meta' => ['request_id'],
            ]);
    }
}
