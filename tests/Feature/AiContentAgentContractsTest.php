<?php

namespace Tests\Feature;

use App\AI\Contracts\ContentAgentInterface;
use App\AI\DTO\AgentErrorData;
use App\AI\DTO\AgentInput;
use App\AI\DTO\AgentResult;
use App\AI\DTO\ContentBriefInput;
use App\AI\DTO\ContentBriefResult;
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
        $input = new TopicDiscoveryInput(
            cluster: 'ai_tools',
            targetCount: 5,
            audience: 'Technical content leads',
            existingTopics: ['Prompt versioning'],
            knowledgeContext: ['Use database-backed prompts.'],
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
    }

    public function test_agent_result_success_can_wrap_structured_output_and_usage_metadata(): void
    {
        $parsed = new ContentBriefResult(
            recommendedTitle: 'AI Prompt Versioning for Editorial Teams',
            slug: 'ai-prompt-versioning-for-editorial-teams',
            metaTitle: 'AI Prompt Versioning for Editorial Teams',
            metaDescription: 'A structured brief for editorial prompt versioning.',
            introAngle: 'Use prompt versioning as an editorial operations control.',
            targetAudience: 'Editorial teams',
            outline: [['heading' => 'Why prompt versioning matters', 'purpose' => 'Frame the problem']],
            headingStructure: ['Why prompt versioning matters'],
            faqSuggestions: [['question' => 'Why version prompts?', 'answer_focus' => 'Operational control']],
            internalLinkSuggestions: [['title' => 'Prompt Ops', 'url' => '/prompt-ops']],
            imageIdeas: ['Editorial prompt workflow diagram'],
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
        $this->assertInstanceOf(ContentBriefResult::class, $result->parsedResponse);
        $this->assertSame('AI Prompt Versioning for Editorial Teams', $result->parsedResponse?->recommendedTitle);
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
