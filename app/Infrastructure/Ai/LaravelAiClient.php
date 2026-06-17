<?php

namespace App\Infrastructure\Ai;

use App\Infrastructure\Ai\Agents\GenericTextAgent;
use App\Infrastructure\Ai\Contracts\AiClient;
use App\Infrastructure\Ai\Data\AiUsageData;
use App\Infrastructure\Ai\Data\GenerateTextRequest;
use App\Infrastructure\Ai\Data\TextGenerationResult;
use App\Infrastructure\Ai\Exceptions\AiCallFailedException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Throwable;

class LaravelAiClient implements AiClient
{
    public function generateText(GenerateTextRequest $request): TextGenerationResult
    {
        $provider = $request->provider ?? $this->defaultProvider();
        $model = $request->model ?? $this->defaultTextModel($provider);
        $timeout = $request->timeoutSeconds ?? $this->defaultTimeout();
        $retryTimes = max(1, $request->retryTimes ?? $this->defaultRetryTimes());
        $retrySleepMilliseconds = max(0, $request->retrySleepMilliseconds ?? $this->defaultRetrySleepMilliseconds());

        $agent = new GenericTextAgent(
            instructions: $request->systemPrompt,
            providerName: $provider,
            modelName: $model,
            timeoutSeconds: $timeout,
        );

        try {
            $response = retry(
                $retryTimes,
                fn () => $agent->prompt($request->prompt),
                $retrySleepMilliseconds,
                fn (Throwable $throwable): bool => $this->shouldRetry($throwable),
            );
        } catch (Throwable $throwable) {
            throw AiCallFailedException::fromThrowable($throwable);
        }

        return new TextGenerationResult(
            content: (string) $response,
            provider: $response->meta->provider,
            model: $response->meta->model,
            usage: new AiUsageData(
                promptTokens: $response->usage->promptTokens,
                completionTokens: $response->usage->completionTokens,
                cacheWriteInputTokens: $response->usage->cacheWriteInputTokens,
                cacheReadInputTokens: $response->usage->cacheReadInputTokens,
                reasoningTokens: $response->usage->reasoningTokens,
            ),
        );
    }

    private function defaultProvider(): string
    {
        return (string) config('ai.service.default_provider', config('ai.default', 'openai'));
    }

    private function defaultTextModel(string $provider): ?string
    {
        $model = config("ai.service.providers.{$provider}.text_model");

        return is_string($model) && $model !== '' ? $model : null;
    }

    private function defaultTimeout(): int
    {
        return max(1, (int) config('ai.service.timeout', 30));
    }

    private function defaultRetryTimes(): int
    {
        return max(1, (int) config('ai.service.retry.times', 2));
    }

    private function defaultRetrySleepMilliseconds(): int
    {
        return max(0, (int) config('ai.service.retry.sleep_ms', 250));
    }

    private function shouldRetry(Throwable $throwable): bool
    {
        return $throwable instanceof ConnectionException
            || $throwable instanceof RequestException
            || $throwable instanceof RateLimitedException
            || $throwable instanceof ProviderOverloadedException;
    }
}
