<?php

namespace Tests\Feature;

use App\AI\Contracts\ContentAgentInterface;
use App\AI\DTO\AgentErrorData;
use App\AI\DTO\AgentInput;
use App\AI\DTO\BlogDraftInput;
use App\AI\DTO\BlogDraftResult;
use App\AI\DTO\AgentResult;
use App\AI\DTO\ContentBriefInput;
use App\AI\DTO\ContentBriefResult;
use App\AI\Enums\BlogDraftGenerationMode;
use App\AI\DTO\TopicDiscoveryInput;
use App\AI\DTO\TopicDiscoveryResult;
use App\AI\DTO\TopicSuggestionData;
use App\AI\Enums\AiRunStatus;
use App\Infrastructure\Ai\Data\AiUsageData;
use Tests\TestCase;

class AiContentAgentContractsTest extends TestCase
{
    public function test_agent_input_can_build_text_generation_request(): void
    {
        $input = new BlogDraftInput(
            contentBriefId: 12,
            contentTopicId: 7,
            title: 'AI Prompt Versioning for Editorial Teams',
            slug: 'ai-prompt-versioning-for-editorial-teams',
            generationMode: BlogDraftGenerationMode::Tutorial->value,
            primaryKeyword: 'ai prompt versioning',
            secondaryKeywords: ['prompt ops'],
            searchIntent: 'informational',
            introAngle: 'Use prompt versioning as an editorial control.',
            outline: [['heading' => 'Why prompt versioning matters', 'purpose' => 'Frame the problem']],
            headingStructure: ['Why prompt versioning matters'],
            faqSuggestions: [['question' => 'Why version prompts?', 'answer_markdown' => 'To keep reviewable changes.']],
            knowledgeBaseContext: ['Use database-backed prompts.'],
            provider: 'openai',
            model: 'gpt-5-mini',
            timeoutSeconds: 20,
            retryTimes: 2,
            retrySleepMilliseconds: 500,
        );

        $request = $input->toGenerateTextRequest(
            systemPrompt: 'You generate blog topic suggestions.',
            prompt: 'Suggest five topics.',
        );

        $this->assertSame('You generate blog topic suggestions.', $request->systemPrompt);
        $this->assertSame('Suggest five topics.', $request->prompt);
        $this->assertSame('openai', $request->provider);
        $this->assertSame('gpt-5-mini', $request->model);
        $this->assertSame(20, $request->timeoutSeconds);
        $this->assertSame(2, $request->retryTimes);
        $this->assertSame(500, $request->retrySleepMilliseconds);
        $this->assertSame(BlogDraftGenerationMode::Tutorial->value, $input->generationMode);
    }

    public function test_agent_result_success_can_wrap_structured_output_and_usage_metadata(): void
    {
        $parsed = new BlogDraftResult(
            title: 'AI Prompt Versioning for Editorial Teams',
            slug: 'ai-prompt-versioning-for-editorial-teams',
            markdownBody: '# AI Prompt Versioning for Editorial Teams',
            excerpt: 'A practical draft for editorial prompt versioning.',
            contentBlocks: [
                ['block_type' => 'heading', 'sort_order' => 1, 'content' => ['text' => 'AI Prompt Versioning for Editorial Teams', 'level' => 1]],
            ],
            seoTitle: 'AI Prompt Versioning for Editorial Teams',
            metaDescription: 'A practical draft for editorial prompt versioning.',
            faqSuggestions: [['question' => 'Why version prompts?', 'answer_markdown' => 'To keep reviewable changes.']],
            suggestedTags: ['Prompt Ops'],
            imagePlacementNotes: ['Use a workflow diagram after the intro.'],
            altTextSuggestions: ['Diagram of prompt review workflow'],
        );

        $result = AgentResult::success(
            agent: 'content-brief',
            rawResponse: '{"recommended_title":"AI Prompt Versioning for Editorial Teams"}',
            parsedResponse: $parsed,
            usage: new AiUsageData(promptTokens: 120, completionTokens: 80),
            provider: 'openai',
            model: 'gpt-5-mini',
            metadata: ['job_id' => 42],
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertFalse($result->isFailure());
        $this->assertSame(AiRunStatus::SUCCESS, $result->status);
        $this->assertSame(120, $result->usage?->promptTokens);
        $this->assertInstanceOf(BlogDraftResult::class, $result->parsedResponse);
        $this->assertSame('AI Prompt Versioning for Editorial Teams', $result->parsedResponse?->title);
        $this->assertSame(42, $result->metadata['job_id']);
    }

    public function test_fake_content_agents_can_be_tested_without_real_ai_provider_calls(): void
    {
        $agent = new class implements ContentAgentInterface
        {
            public function name(): string
            {
                return 'topic-discovery';
            }

            public function run(AgentInput $input): AgentResult
            {
                return AgentResult::success(
                    agent: $this->name(),
                    rawResponse: ['recommended_title' => 'AI Queue Idempotency'],
                    parsedResponse: new ContentBriefResult(
                        recommendedTitle: 'AI Queue Idempotency',
                        slug: 'ai-queue-idempotency',
                        outline: [['heading' => 'Idempotency basics', 'purpose' => 'Explain the concept']],
                        headingStructure: ['Idempotency basics'],
                    ),
                );
            }
        };

        $result = $agent->run(new ContentBriefInput(
            contentTopicId: 7,
            topicTitle: 'AI Queue Idempotency',
            cluster: 'developer_ai',
        ));

        $this->assertTrue($result->isSuccessful());
        $this->assertSame('AI Queue Idempotency', $result->parsedResponse?->recommendedTitle);
    }

    public function test_failed_or_partial_runs_capture_error_details_without_silent_failure(): void
    {
        $parseError = AgentErrorData::fromThrowable(
            new \RuntimeException('Response JSON could not be parsed.'),
            ['stage' => 'parse'],
        );

        $partial = AgentResult::partial(
            agent: 'content-brief',
            rawResponse: 'not valid json',
            error: $parseError,
            provider: 'openai',
            model: 'gpt-5-mini',
        );

        $failed = AgentResult::failed(
            agent: 'blog-writer',
            error: AgentErrorData::fromThrowable(new \RuntimeException('Provider timeout.')),
        );

        $this->assertTrue($partial->isPartial());
        $this->assertSame(AiRunStatus::PARTIAL, $partial->status);
        $this->assertSame('parse', $partial->error?->context['stage']);
        $this->assertSame('Response JSON could not be parsed.', $partial->error?->message);

        $this->assertTrue($failed->isFailure());
        $this->assertSame(AiRunStatus::FAILED, $failed->status);
        $this->assertSame('Provider timeout.', $failed->error?->message);
    }
}
