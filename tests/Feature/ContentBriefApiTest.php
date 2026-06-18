<?php

namespace Tests\Feature;

use App\Infrastructure\Ai\Contracts\AiClient;
use App\Infrastructure\Ai\Data\AiUsageData;
use App\Infrastructure\Ai\Data\GenerateTextRequest;
use App\Infrastructure\Ai\Data\TextGenerationResult;
use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Models\KnowledgeBaseEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentBriefApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_content_brief_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/content-briefs')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_generate_review_update_and_approve_content_briefs_from_approved_topics(): void
    {
        $this->seedPromptAndFakeAgent();

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'AI Editorial Checklists for Content Teams',
            'slug' => 'ai-editorial-checklists-for-content-teams',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'ai editorial checklist',
            'secondary_keywords' => ['content operations', 'editorial workflow'],
            'search_intent' => 'informational',
            'priority_score' => '88.00',
            'difficulty_note' => 'Moderate competition with practical long-tail angle.',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => 'Approved for brief generation.',
            'approved_at' => now(),
        ]);

        KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'title' => 'Editorial QA',
            'slug' => 'editorial-qa',
            'entry_type' => KnowledgeBaseEntry::TYPE_REFERENCE,
            'status' => KnowledgeBaseEntry::STATUS_ACTIVE,
            'summary' => 'Use explicit editorial QA gates.',
            'content_markdown' => 'Detailed QA notes.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => null,
        ]);

        $generateResponse = $this->withToken($token)->postJson("/api/v1/admin/content-topics/{$topic->id}/generate-brief");

        $generateResponse->assertCreated()
            ->assertJsonPath('data.content_topic_id', $topic->id)
            ->assertJsonPath('data.title', 'AI Editorial Checklists for Content Teams')
            ->assertJsonPath('data.status', ContentBrief::STATUS_DRAFT)
            ->assertJsonPath('data.primary_keyword', 'ai editorial checklist')
            ->assertJsonPath('data.search_intent', 'informational')
            ->assertJsonCount(2, 'data.headings')
            ->assertJsonCount(1, 'data.faq_suggestions')
            ->assertJsonCount(2, 'data.image_suggestions')
            ->assertJsonPath('data.can_generate_draft', false);

        $briefId = (int) $generateResponse->json('data.id');

        $this->withToken($token)->postJson("/api/v1/admin/content-topics/{$topic->id}/generate-brief")
            ->assertOk()
            ->assertJsonPath('data.id', $briefId);

        $this->withToken($token)->getJson('/api/v1/admin/content-briefs?status=draft&content_topic_id='.$topic->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $briefId);

        $this->withToken($token)->getJson("/api/v1/admin/content-briefs/{$briefId}")
            ->assertOk()
            ->assertJsonPath('data.topic.id', $topic->id)
            ->assertJsonPath('data.topic.status', ContentTopic::STATUS_APPROVED);

        $this->withToken($token)->patchJson("/api/v1/admin/content-briefs/{$briefId}", [
            'meta_title' => 'AI Editorial Checklists for Content Teams',
            'meta_description' => 'A practical brief for editorial teams building AI review checklists.',
            'headings' => [
                'Why AI editorial checklists matter',
                'A practical checklist framework',
            ],
            'outline' => [
                ['heading' => 'Why AI editorial checklists matter', 'purpose' => 'Frame the workflow'],
                ['heading' => 'A practical checklist framework', 'purpose' => 'Give the implementation plan'],
            ],
            'faq_suggestions' => [
                ['question' => 'What should an editorial AI checklist include?', 'answer_focus' => 'Review gates and policy checks'],
            ],
            'image_suggestions' => [
                ['placement' => 'hero', 'idea' => 'Checklist board', 'alt_text' => 'Editorial checklist board with AI review steps'],
            ],
            'status' => ContentBrief::STATUS_REJECTED,
        ])->assertOk()
            ->assertJsonPath('data.status', ContentBrief::STATUS_REJECTED)
            ->assertJsonPath('data.headings.1', 'A practical checklist framework')
            ->assertJsonPath('data.image_suggestions.0.alt_text', 'Editorial checklist board with AI review steps');

        $this->withToken($token)->postJson("/api/v1/admin/content-briefs/{$briefId}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', ContentBrief::STATUS_APPROVED)
            ->assertJsonPath('data.can_generate_draft', true);

        $this->assertDatabaseHas('content_briefs', [
            'id' => $briefId,
            'content_topic_id' => $topic->id,
            'status' => ContentBrief::STATUS_APPROVED,
            'primary_keyword' => 'ai editorial checklist',
        ]);
    }

    public function test_content_brief_generation_is_rejected_for_non_approved_topics(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'Agent Review Loops',
            'slug' => 'agent-review-loops',
            'cluster' => ContentTopic::CLUSTER_DEVELOPER_AI,
            'primary_keyword' => 'agent review loops',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '72.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_SUGGESTED,
            'notes' => null,
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/content-topics/{$topic->id}/generate-brief")
            ->assertStatus(409)
            ->assertJsonPath('error_code', 'CONFLICT')
            ->assertJsonPath('errors.status.0', ContentTopic::STATUS_SUGGESTED)
            ->assertJsonPath('errors.action.0', 'generate-brief');
    }

    public function test_content_brief_validation_errors_use_consistent_json_shape(): void
    {
        $this->seedPromptAndFakeAgent();

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $topic = ContentTopic::query()->create([
            'title' => 'AI Search Intent Maps',
            'slug' => 'ai-search-intent-maps',
            'cluster' => ContentTopic::CLUSTER_SEO,
            'primary_keyword' => 'ai search intent',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '70.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => null,
            'approved_at' => now(),
        ]);

        $briefId = (int) $this->withToken($token)
            ->postJson("/api/v1/admin/content-topics/{$topic->id}/generate-brief")
            ->json('data.id');

        $this->withToken($token)->patchJson("/api/v1/admin/content-briefs/{$briefId}", [
            'headings' => ['Valid', 123],
            'status' => ContentBrief::STATUS_APPROVED,
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['headings.1', 'status'],
            'meta' => ['request_id'],
            ]);
    }

    private function seedPromptAndFakeAgent(): void
    {
        $template = AiPromptTemplate::query()->create([
            'name' => 'Content Brief Default',
            'key' => 'content_brief_default',
            'type' => AiPromptTemplate::TYPE_CONTENT_BRIEF,
            'description' => 'Default brief prompt.',
            'status' => AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $version = AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $template->id,
            'version' => 1,
            'system_prompt' => 'Build a structured brief for {{topic_title}}.',
            'user_prompt' => 'Knowledge {{knowledge_context}} Existing {{existing_post_context}} Links {{internal_link_context}}',
            'output_schema' => ['type' => 'object'],
            'variables' => ['topic_title', 'knowledge_context', 'existing_post_context', 'internal_link_context'],
            'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
        ]);

        $template->update(['active_version_id' => $version->id]);

        $fakeClient = new class implements AiClient
        {
            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                return new TextGenerationResult(
                    content: json_encode([
                        'recommended_title' => 'AI Editorial Checklists for Content Teams',
                        'slug' => 'ai-editorial-checklists-for-content-teams',
                        'meta_title' => 'AI Editorial Checklists for Content Teams',
                        'meta_description' => 'A practical brief for editorial teams building AI review checklists.',
                        'intro_angle' => 'Use editorial checklists to review AI-assisted publishing safely.',
                        'target_audience' => 'Editorial leads and content operators',
                        'outline' => [
                            ['heading' => 'Why AI editorial checklists matter', 'purpose' => 'Frame the workflow'],
                            ['heading' => 'A practical checklist framework', 'purpose' => 'Give the implementation plan'],
                        ],
                        'heading_structure' => [
                            'Why AI editorial checklists matter',
                            'A practical checklist framework',
                        ],
                        'faq_suggestions' => [
                            ['question' => 'What should an editorial AI checklist include?', 'answer_focus' => 'Review gates and policy checks'],
                        ],
                        'internal_link_suggestions' => [
                            ['title' => 'Editorial QA', 'url' => '/knowledge/editorial-qa', 'reason' => 'QA overlap'],
                        ],
                        'image_ideas' => [
                            'Checklist board',
                            'Approval workflow diagram',
                        ],
                        'alt_text_suggestions' => [
                            'Editorial checklist board with AI review steps',
                            'Diagram of approval workflow for AI-assisted content',
                        ],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'openai',
                    model: 'gpt-5-mini',
                    usage: new AiUsageData(promptTokens: 100, completionTokens: 60),
                );
            }
        };

        $this->app->instance(AiClient::class, $fakeClient);
    }
}
