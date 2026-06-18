<?php

namespace App\Modules\Ai\Services;

use App\Infrastructure\Ai\Data\AiUsageData;
use App\Models\AiGenerationStep;
use App\Models\AiJob;
use App\Models\AiJobCost;
use App\Modules\Ai\Data\CreateAiJobCostData;
use App\Modules\Ai\Repositories\AiJobCostRepository;
use Illuminate\Support\Collection;

class RecordAiUsageService
{
    public function __construct(
        private readonly AiJobCostRepository $costs,
    ) {}

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function recordForJob(
        AiJob $job,
        AiUsageData|array|null $usage = null,
        ?string $provider = null,
        ?string $model = null,
        ?string $actualCost = null,
        ?array $metadata = null,
    ): AiJobCost {
        $normalized = $this->normalizeUsage($usage);
        $providerName = $provider ?? $job->provider;
        $modelName = $model ?? $job->model;
        $currency = $this->defaultCurrency();

        return $this->costs->updateJobAggregate($job, new CreateAiJobCostData(
            aiJobId: (int) $job->id,
            provider: $providerName,
            model: $modelName,
            inputTokens: $normalized['input_tokens'],
            outputTokens: $normalized['output_tokens'],
            totalTokens: $normalized['total_tokens'],
            estimatedCost: $this->estimateCost($providerName, $modelName, $normalized['input_tokens'], $normalized['output_tokens']),
            actualCost: $actualCost,
            currency: $currency,
            metadata: array_merge($normalized['metadata'], $metadata ?? [], ['scope' => 'job']),
        ));
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function recordForStep(
        AiJob $job,
        AiGenerationStep $step,
        AiUsageData|array|null $usage = null,
        ?string $provider = null,
        ?string $model = null,
        ?string $actualCost = null,
        ?array $metadata = null,
    ): AiJobCost {
        $normalized = $this->normalizeUsage($usage);
        $providerName = $provider ?? $job->provider;
        $modelName = $model ?? $job->model;
        $currency = $this->defaultCurrency();

        $cost = $this->costs->create(new CreateAiJobCostData(
            aiJobId: (int) $job->id,
            aiGenerationStepId: (int) $step->id,
            provider: $providerName,
            model: $modelName,
            inputTokens: $normalized['input_tokens'],
            outputTokens: $normalized['output_tokens'],
            totalTokens: $normalized['total_tokens'],
            estimatedCost: $this->estimateCost($providerName, $modelName, $normalized['input_tokens'], $normalized['output_tokens']),
            actualCost: $actualCost,
            currency: $currency,
            metadata: array_merge($normalized['metadata'], $metadata ?? [], ['scope' => 'step']),
        ));

        $this->refreshJobAggregate($job, $providerName, $modelName);

        return $cost;
    }

    private function refreshJobAggregate(AiJob $job, ?string $provider = null, ?string $model = null): AiJobCost
    {
        /** @var Collection<int, AiJobCost> $stepCosts */
        $stepCosts = $this->costs->findByJob($job)
            ->filter(fn (AiJobCost $cost): bool => $cost->ai_generation_step_id !== null)
            ->values();

        $inputTokens = $stepCosts->sum('input_tokens');
        $outputTokens = $stepCosts->sum('output_tokens');
        $totalTokens = $stepCosts->sum('total_tokens');
        $estimatedCost = $stepCosts->reduce(
            fn (string $carry, AiJobCost $cost): string => bcadd($carry, (string) ($cost->estimated_cost ?? '0'), 8),
            '0',
        );
        $actualCost = $stepCosts->contains(fn (AiJobCost $cost): bool => $cost->actual_cost !== null)
            ? $stepCosts->reduce(
                fn (string $carry, AiJobCost $cost): string => bcadd($carry, (string) ($cost->actual_cost ?? '0'), 8),
                '0',
            )
            : null;

        return $this->costs->updateJobAggregate($job, new CreateAiJobCostData(
            aiJobId: (int) $job->id,
            provider: $provider ?? $job->provider,
            model: $model ?? $job->model,
            inputTokens: $inputTokens,
            outputTokens: $outputTokens,
            totalTokens: $totalTokens,
            estimatedCost: $estimatedCost,
            actualCost: $actualCost,
            currency: $this->defaultCurrency(),
            metadata: ['scope' => 'job_aggregate', 'step_cost_count' => $stepCosts->count()],
        ));
    }

    /**
     * @return array{input_tokens:int,output_tokens:int,total_tokens:int,metadata:array<string, mixed>}
     */
    private function normalizeUsage(AiUsageData|array|null $usage): array
    {
        if ($usage instanceof AiUsageData) {
            $usage = $usage->toArray();
        }

        $usage = is_array($usage) ? $usage : [];

        $promptTokens = max(0, (int) ($usage['prompt_tokens'] ?? 0));
        $completionTokens = max(0, (int) ($usage['completion_tokens'] ?? 0));
        $cacheWriteTokens = max(0, (int) ($usage['cache_write_input_tokens'] ?? 0));
        $cacheReadTokens = max(0, (int) ($usage['cache_read_input_tokens'] ?? 0));
        $reasoningTokens = max(0, (int) ($usage['reasoning_tokens'] ?? 0));

        $inputTokens = $promptTokens + $cacheWriteTokens + $cacheReadTokens;
        $outputTokens = $completionTokens + $reasoningTokens;
        $totalTokens = $inputTokens + $outputTokens;

        return [
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $totalTokens,
            'metadata' => [
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'cache_write_input_tokens' => $cacheWriteTokens,
                'cache_read_input_tokens' => $cacheReadTokens,
                'reasoning_tokens' => $reasoningTokens,
            ],
        ];
    }

    private function estimateCost(?string $provider, ?string $model, int $inputTokens, int $outputTokens): ?string
    {
        if ($provider === null || $model === null) {
            return $inputTokens === 0 && $outputTokens === 0 ? '0.00000000' : null;
        }

        /** @var array{input_per_1k_tokens?:float|int|string,output_per_1k_tokens?:float|int|string}|null $pricing */
        $pricing = config("ai.service.pricing.providers.{$provider}.models.{$model}");

        if (! is_array($pricing)) {
            return $inputTokens === 0 && $outputTokens === 0 ? '0.00000000' : null;
        }

        $inputRate = (string) ($pricing['input_per_1k_tokens'] ?? '0');
        $outputRate = (string) ($pricing['output_per_1k_tokens'] ?? '0');

        $inputCost = bcmul(bcdiv((string) $inputTokens, '1000', 8), $inputRate, 8);
        $outputCost = bcmul(bcdiv((string) $outputTokens, '1000', 8), $outputRate, 8);

        return bcadd($inputCost, $outputCost, 8);
    }

    private function defaultCurrency(): ?string
    {
        $currency = config('ai.service.pricing.default_currency');

        return is_string($currency) && $currency !== '' ? $currency : null;
    }
}
