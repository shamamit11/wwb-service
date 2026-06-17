<?php

namespace Tests\Feature;

use App\Infrastructure\Ai\Agents\GenericTextAgent;
use App\Infrastructure\Ai\Contracts\AiClient;
use App\Infrastructure\Ai\Data\AiUsageData;
use App\Infrastructure\Ai\Data\GenerateTextRequest;
use App\Infrastructure\Ai\Data\TextGenerationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaravelAiClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_client_is_resolved_through_internal_contract_and_uses_fakeable_agent(): void
    {
        config()->set('ai.service.default_provider', 'openai');
        config()->set('ai.service.providers.openai.text_model', 'gpt-5-mini');
        config()->set('ai.service.timeout', 15);
        config()->set('ai.service.retry.times', 1);

        GenericTextAgent::fake([
            'Generated draft copy.',
        ])->preventStrayPrompts();

        $response = app(AiClient::class)->generateText(new GenerateTextRequest(
            systemPrompt: 'You write structured blog drafts.',
            prompt: 'Draft an outline about AI content pipelines.',
        ));

        $this->assertSame('Generated draft copy.', $response->content);
        $this->assertSame('openai', $response->provider);
        $this->assertSame('gpt-5-mini', $response->model);
        $this->assertSame(0, $response->usage->promptTokens);

        GenericTextAgent::assertPrompted('Draft an outline about AI content pipelines.');
    }

    public function test_ai_contract_can_be_rebound_for_domain_level_fakes(): void
    {
        $fake = new class implements AiClient
        {
            public array $requests = [];

            public function generateText(GenerateTextRequest $request): TextGenerationResult
            {
                $this->requests[] = $request;

                return new TextGenerationResult(
                    content: 'Fake domain-safe response.',
                    provider: 'fake',
                    model: 'fake-model',
                    usage: new AiUsageData,
                );
            }
        };

        $this->app->instance(AiClient::class, $fake);

        $response = app(AiClient::class)->generateText(new GenerateTextRequest(
            systemPrompt: 'System',
            prompt: 'Prompt',
        ));

        $this->assertSame('Fake domain-safe response.', $response->content);
        $this->assertCount(1, $fake->requests);
        $this->assertSame('Prompt', $fake->requests[0]->prompt);
    }
}
