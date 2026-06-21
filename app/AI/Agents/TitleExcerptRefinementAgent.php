<?php

namespace App\AI\Agents;

use App\AI\Contracts\ContentAgentInterface;
use App\AI\DTO\AgentErrorData;
use App\AI\DTO\AgentInput;
use App\AI\DTO\AgentResult;
use App\AI\DTO\PostTitleExcerptRefinementInput;
use App\AI\DTO\PostTitleExcerptRefinementResult;
use App\AI\Support\DecodesJsonResponse;
use App\Infrastructure\Ai\Contracts\AiClient;
use App\Models\AiJob;
use App\Modules\Ai\Data\CreateAiGenerationStepData;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\Ai\Services\RecordAiUsageService;
use App\Modules\Ai\Services\TrackAiJobService;
use RuntimeException;
use Throwable;

class TitleExcerptRefinementAgent implements ContentAgentInterface
{
    use DecodesJsonResponse;

    private const JOB_TYPE = 'post_title_excerpt_refinement';

    public function __construct(
        private readonly AiClient $aiClient,
        private readonly AiJobRepository $jobs,
        private readonly TrackAiJobService $trackAiJob,
        private readonly RecordAiUsageService $recordAiUsage,
    ) {}

    public function name(): string
    {
        return 'TitleExcerptRefinementAgent';
    }

    public function run(AgentInput $input): AgentResult
    {
        if (! $input instanceof PostTitleExcerptRefinementInput) {
            throw new RuntimeException('TitleExcerptRefinementAgent requires a PostTitleExcerptRefinementInput instance.');
        }

        $job = $this->resolveOrCreateJob($input);
        $step = $this->trackAiJob->createStep(new CreateAiGenerationStepData(
            aiJobId: (int) $job->id,
            agentName: $this->name(),
            inputPayload: $this->buildJobInputPayload($input),
        ));
        $step = $this->trackAiJob->startStep($step);

        try {
            $response = $this->aiClient->generateText($input->toGenerateTextRequest(
                systemPrompt: $this->buildSystemPrompt(),
                prompt: $this->buildUserPrompt($input),
            ));

            $parsedResponse = $this->parseResponse($response->content);
            $usagePayload = $response->usage->toArray();
            $outputPayload = [
                'post_id' => $input->postId,
                'recommended_title' => $parsedResponse->recommendedTitle,
                'recommended_excerpt' => $parsedResponse->recommendedExcerpt,
                'headline_variations' => $parsedResponse->headlineVariations,
                'excerpt_variations' => $parsedResponse->excerptVariations,
                'rationale' => $parsedResponse->rationale,
            ];

            $step = $this->trackAiJob->completeStep($step, $outputPayload, $usagePayload);
            $job = $this->trackAiJob->completeJob($job, $outputPayload, $usagePayload);
            $this->recordAiUsage->recordForStep($job, $step, $response->usage, $response->provider, $response->model);

            return AgentResult::success(
                agent: $this->name(),
                rawResponse: $this->decodeJson($response->content) ?? $response->content,
                parsedResponse: $parsedResponse,
                usage: $response->usage,
                provider: $response->provider,
                model: $response->model,
                metadata: [
                    'job_id' => (int) $job->id,
                    'step_id' => (int) $step->id,
                    'post_id' => $input->postId,
                ],
            );
        } catch (Throwable $throwable) {
            $error = AgentErrorData::fromThrowable($throwable);

            $this->trackAiJob->failStep($step, $error->message, ['error' => $error->toArray()]);
            $this->trackAiJob->failJob($job, $error->message, ['error' => $error->toArray()]);

            return AgentResult::failed(
                agent: $this->name(),
                error: $error,
                provider: $job->provider,
                model: $job->model,
                metadata: [
                    'job_id' => (int) $job->id,
                    'step_id' => (int) $step->id,
                    'post_id' => $input->postId,
                ],
            );
        }
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an editorial copy assistant for a professional blog.

Return valid JSON only with this shape:
{
  "recommended_title": "best improved title",
  "recommended_excerpt": "best improved short description",
  "headline_variations": ["alternative title"],
  "excerpt_variations": ["alternative excerpt"],
  "rationale": "short explanation"
}

Rules:
- Keep the voice credible, specific, and professional.
- Avoid clickbait and exaggerated claims.
- Optimize for clarity and search intent, not gimmicks.
- Provide concise variations that remain faithful to the article.
PROMPT;
    }

    private function buildUserPrompt(PostTitleExcerptRefinementInput $input): string
    {
        $sections = [
            'Post title: '.$input->postTitle,
            'Post slug: '.$input->postSlug,
            'Post status: '.$input->postStatus,
            'Current short description: '.$this->stringOrFallback($input->postExcerpt),
            'Primary keyword: '.$this->stringOrFallback($input->primaryKeyword),
            'Secondary keywords: '.$this->listOrFallback($input->secondaryKeywords),
            'Existing tags: '.$this->listOrFallback($input->existingTags),
            'Knowledge base context: '.$this->listOrFallback($input->knowledgeBaseContext),
            'Editorial instructions: '.$this->stringOrFallback($input->instructions),
            'Article markdown:'."\n".$input->existingMarkdownBody,
        ];

        return implode("\n\n", $sections);
    }

    private function parseResponse(string $rawContent): PostTitleExcerptRefinementResult
    {
        $decoded = $this->decodeJson($rawContent);

        if (! is_array($decoded)) {
            throw new RuntimeException('Title/excerpt refinement response was not valid JSON.');
        }

        return new PostTitleExcerptRefinementResult(
            recommendedTitle: $this->normalizeString($decoded['recommended_title'] ?? null),
            recommendedExcerpt: $this->normalizeString($decoded['recommended_excerpt'] ?? null),
            headlineVariations: $this->normalizeStringList($decoded['headline_variations'] ?? []),
            excerptVariations: $this->normalizeStringList($decoded['excerpt_variations'] ?? []),
            rationale: $this->normalizeString($decoded['rationale'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildJobInputPayload(PostTitleExcerptRefinementInput $input): array
    {
        return [
            'post_id' => $input->postId,
            'instructions' => $input->instructions,
        ];
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    private function normalizeStringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $item): ?string => $this->normalizeString($item),
            $value,
        )));
    }

    /**
     * @param  list<string>  $values
     */
    private function listOrFallback(array $values): string
    {
        $values = array_values(array_filter(array_map(
            fn (mixed $item): ?string => $this->normalizeString($item),
            $values,
        )));

        return $values === [] ? 'none' : implode(', ', $values);
    }

    private function stringOrFallback(?string $value): string
    {
        return $this->normalizeString($value) ?? 'none';
    }

    private function resolveProvider(PostTitleExcerptRefinementInput $input): ?string
    {
        return $input->provider
            ?? config('ai.service.default_provider')
            ?? config('ai.default');
    }

    private function resolveModel(PostTitleExcerptRefinementInput $input): ?string
    {
        if (is_string($input->model) && $input->model !== '') {
            return $input->model;
        }

        $provider = $this->resolveProvider($input);

        if (! is_string($provider) || $provider === '') {
            return null;
        }

        $model = config("ai.service.providers.{$provider}.text_model");

        return is_string($model) && $model !== '' ? $model : null;
    }

    private function resolveOrCreateJob(PostTitleExcerptRefinementInput $input): AiJob
    {
        $existingJobId = $input->metadata['ai_job_id'] ?? null;

        if (is_int($existingJobId) || (is_string($existingJobId) && ctype_digit($existingJobId))) {
            $job = $this->jobs->findById((int) $existingJobId);

            if ($job === null) {
                throw new RuntimeException("AI job [{$existingJobId}] could not be found.");
            }

            return $this->trackAiJob->startJob($job);
        }

        $job = $this->jobs->create(new CreateAiJobData(
            type: self::JOB_TYPE,
            status: AiJob::STATUS_PENDING,
            entityType: 'post',
            entityId: $input->postId,
            provider: $this->resolveProvider($input),
            model: $this->resolveModel($input),
            inputPayload: $this->buildJobInputPayload($input),
        ));

        return $this->trackAiJob->startJob($job);
    }
}
