<?php

namespace Tests\Feature;

use App\AI\Agents\ContentBriefAgent;
use App\AI\DTO\ContentBriefInput;
use App\Infrastructure\Ai\Contracts\AiClient;
use App\Infrastructure\Ai\Data\AiUsageData;
use App\Infrastructure\Ai\Data\GenerateTextRequest;
use App\Infrastructure\Ai\Data\TextGenerationResult;
use App\Models\AiGenerationStep;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Models\KnowledgeBaseEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentBriefAgentTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_brief_agent_uses_context_saves_brief_and_tracks_ai_workflow(): void
    {
        config()->set('ai.service.default_provider', 'openai');
        config()->set('ai.service.providers.openai.text_model', 'gpt-5-mini');
        config()->set('ai.service.pricing.default_currency', 'USD');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.input_per_1k_tokens', '0.001');
        config()->set('ai.service.pricing.providers.openai.models.gpt-5-mini.output_per_1k_tokens', '0.003');

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
            'system_prompt' => 'Build a structured brief for {{topic_title}} in {{cluster}}.',
            'user_prompt' => 'Knowledge {{knowledge_context}} Existing {{existing_post_context}} Links {{internal_link_context}}',
            'output_schema' => ['type' => 'object', 'required' => ['recommended_title', 'outline', 'heading_structure']],
            'variables' => ['topic_title', 'cluster', 'knowledge_context', 'existing_post_context', 'internal_link_context'],
            'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
        ]);

        $template->update(['active_version_id' => $version->id]);

        $topic = ContentTopic::query()->create([
            'title' => 'AI Editorial Review Checklists',
            'slug' => 'ai-editorial-review-checklists',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'ai editorial review checklist',
            'secondary_keywords' => ['editorial workflow', 'review systems'],
            'search_intent' => 'informational',
            'priority_score' => '88.00',
            'difficulty_note' => 'Practical ops angle.',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => 'Approved for brief generation.',
            'approved_at' => now(),
        ]);

        $author = User::factory()->create();
        KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
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

        $fakeClient = new class implements AiClient
        {
            public ?GenerateTextRequest $request = null;

            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                $this->request = $request;

                return new TextGenerationResult(
                    content: json_encode([
                        'recommended_title' => 'AI Editorial Review Checklists for Content Teams',
                        'slug' => 'ai-editorial-review-checklists-for-content-teams',
                        'meta_title' => 'AI Editorial Review Checklists for Content Teams',
                        'meta_description' => 'A structured content brief for editorial review checklists.',
                        'intro_angle' => 'Use review checklists to keep AI-assisted publishing accurate.',
                        'target_audience' => 'Editorial operators and content leads',
                        'outline' => [
                            ['heading' => 'Why AI review checklists matter', 'purpose' => 'Frame the problem'],
                            ['heading' => 'Checklist design principles', 'purpose' => 'Provide the method'],
                        ],
                        'heading_structure' => [
                            'Why AI review checklists matter',
                            'Checklist design principles',
                        ],
                        'faq_suggestions' => [
                            ['question' => 'What belongs in an AI review checklist?', 'answer_focus' => 'Review criteria'],
                        ],
                        'internal_link_suggestions' => [
                            ['title' => 'Editorial QA', 'url' => '/knowledge/editorial-qa', 'reason' => 'QA overlap'],
                        ],
                        'image_ideas' => [
                            'Hero diagram showing editorial review workflow',
                            'Checklist snapshot with approval stages',
                        ],
                        'alt_text_suggestions' => [
                            'Diagram of editorial review workflow for AI content',
                            'Checklist image showing review and approval stages',
                        ],
                    ], JSON_THROW_ON_ERROR),
                    provider: 'openai',
                    model: 'gpt-5-mini',
                    usage: new AiUsageData(promptTokens: 140, completionTokens: 90),
                );
            }
        };

        $this->app->instance(AiClient::class, $fakeClient);

        $result = app(ContentBriefAgent::class)->run(new ContentBriefInput(
            contentTopicId: (int) $topic->id,
            topicTitle: $topic->title,
            cluster: $topic->cluster,
            primaryKeyword: $topic->primary_keyword,
            secondaryKeywords: $topic->secondary_keywords ?? [],
            searchIntent: $topic->search_intent,
            knowledgeBaseContext: ['Editorial QA: Use explicit editorial QA gates.'],
            editorialIntent: $topic->notes,
        ));

        $this->assertTrue($result->isSuccessful());
        $this->assertNotNull($fakeClient->request);
        $this->assertStringContainsString('AI Editorial Review Checklists', $fakeClient->request?->systemPrompt ?? '');
        $this->assertStringContainsString('Editorial QA: Use explicit editorial QA gates.', $fakeClient->request?->prompt ?? '');

        $briefId = $result->metadata['brief_id'];

        $this->assertDatabaseHas('content_briefs', [
            'id' => $briefId,
            'content_topic_id' => $topic->id,
            'title' => 'AI Editorial Review Checklists for Content Teams',
            'primary_keyword' => 'ai editorial review checklist',
            'status' => ContentBrief::STATUS_DRAFT,
        ]);

        $brief = ContentBrief::query()->findOrFail($briefId);
        $this->assertSame('AI Editorial Review Checklists for Content Teams', $brief->meta_title);
        $this->assertCount(2, $brief->image_suggestions ?? []);

        $jobId = $result->metadata['job_id'];
        $stepId = $result->metadata['step_id'];

        $this->assertDatabaseHas('ai_jobs', [
            'id' => $jobId,
            'type' => AiPromptTemplate::TYPE_CONTENT_BRIEF,
            'status' => AiJob::STATUS_COMPLETED,
            'entity_type' => 'content_topic',
            'entity_id' => $topic->id,
        ]);

        $this->assertDatabaseHas('ai_generation_steps', [
            'id' => $stepId,
            'ai_job_id' => $jobId,
            'agent_name' => 'ContentBriefAgent',
            'status' => AiGenerationStep::STATUS_COMPLETED,
        ]);

        $this->assertDatabaseCount('ai_job_costs', 2);
    }
}
