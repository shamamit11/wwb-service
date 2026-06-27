<?php

namespace App\AI\Agents;

use App\AI\Contracts\ContentAgentInterface;
use App\AI\DTO\AgentErrorData;
use App\AI\DTO\AgentInput;
use App\AI\DTO\AgentResult;
use App\AI\DTO\PostMetadataSuggestionInput;
use App\AI\DTO\PostMetadataSuggestionResult;
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

class MetadataSuggestionAgent implements ContentAgentInterface
{
    use DecodesJsonResponse;

    private const JOB_TYPE = 'post_metadata_suggestion';

    public function __construct(
        private readonly AiClient $aiClient,
        private readonly AiJobRepository $jobs,
        private readonly TrackAiJobService $trackAiJob,
        private readonly RecordAiUsageService $recordAiUsage,
    ) {}

    public function name(): string
    {
        return 'MetadataSuggestionAgent';
    }

    public function run(AgentInput $input): AgentResult
    {
        if (! $input instanceof PostMetadataSuggestionInput) {
            throw new RuntimeException('MetadataSuggestionAgent requires a PostMetadataSuggestionInput instance.');
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

            $parsedResponse = $this->parseResponse($response->content, $input);
            $usagePayload = $response->usage->toArray();
            $outputPayload = [
                'post_id' => $input->postId,
                'title' => $parsedResponse->title,
                'excerpt' => $parsedResponse->excerpt,
                'meta_title' => $parsedResponse->metaTitle,
                'meta_description' => $parsedResponse->metaDescription,
                'focus_keyword' => $parsedResponse->focusKeyword,
                'schema_hints' => $parsedResponse->schemaHints,
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
You are an SEO-focused editorial assistant for a professional blog.

Return valid JSON only with this shape:
{
  "title": "optional improved public title",
  "excerpt": "optional improved short description",
  "meta_title": "SEO title up to 60 characters",
  "meta_description": "SEO description up to 160 characters",
  "focus_keyword": "single best focus keyword",
  "schema_hints": ["optional hint"],
  "rationale": "short explanation"
}

Rules:
- Keep recommendations specific, professional, and aligned with the article content.
- Avoid hype, clickbait, and keyword stuffing.
- If the current title or excerpt is already strong, you may return a minimally changed version.
- `schema_hints` must be a short list of useful structured-data or FAQ suggestions.
PROMPT;
    }

    private function buildUserPrompt(PostMetadataSuggestionInput $input): string
    {
        $sections = [
            'Post title: '.$input->postTitle,
            'Post slug: '.$input->postSlug,
            'Post status: '.$input->postStatus,
            'Current short description: '.$this->stringOrFallback($input->postExcerpt),
            'Primary keyword: '.$this->stringOrFallback($input->primaryKeyword),
            'Secondary keywords: '.$this->listOrFallback($input->secondaryKeywords),
            'Existing focus keyword: '.$this->stringOrFallback($input->existingFocusKeyword),
            'Existing meta title: '.$this->stringOrFallback($input->existingMetaTitle),
            'Existing meta description: '.$this->stringOrFallback($input->existingMetaDescription),
            'Existing tags: '.$this->listOrFallback($input->existingTags),
            'Knowledge base context: '.$this->listOrFallback($input->knowledgeBaseContext),
            'Editorial instructions: '.$this->stringOrFallback($input->instructions),
            'Article body:'."\n".$input->existingArticleBody,
        ];

        return implode("\n\n", $sections);
    }

    private function parseResponse(string $rawContent, PostMetadataSuggestionInput $input): PostMetadataSuggestionResult
    {
        $decoded = $this->decodeJson($rawContent);

        if (! is_array($decoded)) {
            throw new RuntimeException('Metadata suggestion response was not valid JSON.');
        }

        return new PostMetadataSuggestionResult(
            title: $this->normalizeString($decoded['title'] ?? null),
            excerpt: $this->normalizeString($decoded['excerpt'] ?? null),
            metaTitle: $this->normalizeString($decoded['meta_title'] ?? null),
            metaDescription: $this->normalizeString($decoded['meta_description'] ?? null),
            focusKeyword: $this->normalizeString($decoded['focus_keyword'] ?? null),
            schemaHints: $this->normalizeStringList($decoded['schema_hints'] ?? []),
            rationale: $this->normalizeString($decoded['rationale'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildJobInputPayload(PostMetadataSuggestionInput $input): array
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

    private function resolveProvider(PostMetadataSuggestionInput $input): ?string
    {
        return $input->provider
            ?? config('ai.service.default_provider')
            ?? config('ai.default');
    }

    private function resolveModel(PostMetadataSuggestionInput $input): ?string
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

    private function resolveOrCreateJob(PostMetadataSuggestionInput $input): AiJob
    {
        $existingJobId = $input->metadata['ai_job_id'] ?? null;

        if (is_int($existingJobId) || (is_string($existingJobId) && ctype_digit($existingJobId))) {
            $job = $this->jobs->findById((int) $existingJobId);

            if ($job === null) {
                throw new RuntimeException("Queued AI job [{$existingJobId}] could not be found.");
            }

            return $this->trackAiJob->startJob($job);
        }

        $job = $this->trackAiJob->createJob(new CreateAiJobData(
            type: self::JOB_TYPE,
            status: AiJob::STATUS_PENDING,
            entityType: 'post',
            entityId: $input->postId,
            provider: $this->resolveProvider($input),
            model: $this->resolveModel($input),
            inputPayload: $this->buildJobInputPayload($input),
        ));

        $job = $this->trackAiJob->queueJob($job);

        return $this->trackAiJob->startJob($job);
    }
}
