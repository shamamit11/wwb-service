<?php

namespace App\AI\Agents;

use App\AI\Contracts\ContentAgentInterface;
use App\AI\DTO\AgentErrorData;
use App\AI\DTO\AgentInput;
use App\AI\DTO\AgentResult;
use App\AI\DTO\PostTitleExcerptRefinementInput;
use App\AI\DTO\PostTitleExcerptRefinementResult;
use App\Infrastructure\Ai\Contracts\AiClient;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Modules\Ai\Data\CreateAiGenerationStepData;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\Ai\Repositories\AiPromptTemplateRepository;
use App\Modules\Ai\Services\RecordAiUsageService;
use App\Modules\Ai\Services\RenderAiPromptTemplateService;
use App\Modules\Ai\Services\TrackAiJobService;
use RuntimeException;
use Throwable;

class TitleExcerptRefinementAgent implements ContentAgentInterface
{
    private const DEFAULT_PROMPT_KEY = 'post_title_excerpt_refinement_default';

    public function __construct(
        private readonly AiClient $aiClient,
        private readonly AiPromptTemplateRepository $promptTemplates,
        private readonly AiJobRepository $jobs,
        private readonly RenderAiPromptTemplateService $renderPrompt,
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
            $promptTemplate = $this->resolvePromptTemplate($input);
            $renderedPrompt = $this->renderPrompt->render($promptTemplate, $this->buildPromptVariables($input));

            if ($renderedPrompt->missingVariables !== []) {
                throw new RuntimeException('Prompt template is missing required variables: '.implode(', ', $renderedPrompt->missingVariables));
            }

            $response = $this->aiClient->generateText($input->toGenerateTextRequest(
                systemPrompt: $renderedPrompt->systemPrompt,
                prompt: $renderedPrompt->userPrompt,
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

    private function resolvePromptTemplate(PostTitleExcerptRefinementInput $input): AiPromptTemplate
    {
        $promptKey = $input->metadata['prompt_template_key'] ?? self::DEFAULT_PROMPT_KEY;
        $promptKey = is_string($promptKey) && $promptKey !== '' ? $promptKey : self::DEFAULT_PROMPT_KEY;

        $template = $this->promptTemplates->findByKey($promptKey)
            ?? $this->promptTemplates->findActiveByType(AiPromptTemplate::TYPE_EDITORIAL_REFINER);

        if (! $template instanceof AiPromptTemplate || ! $template->activeVersion) {
            throw new RuntimeException('No active title/excerpt refinement prompt template is configured.');
        }

        return $template;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPromptVariables(PostTitleExcerptRefinementInput $input): array
    {
        return [
            'post_title' => $input->postTitle,
            'post_slug' => $input->postSlug,
            'post_excerpt' => $input->postExcerpt,
            'post_status' => $input->postStatus,
            'primary_keyword' => $input->primaryKeyword,
            'secondary_keywords' => $input->secondaryKeywords,
            'existing_markdown_body' => $input->existingMarkdownBody,
            'existing_tags' => $input->existingTags,
            'knowledge_context' => $input->knowledgeBaseContext,
            'brief_outline' => $input->briefOutline,
            'brief_headings' => $input->briefHeadings,
            'instructions' => $input->instructions,
        ];
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
            'prompt_template_key' => $input->metadata['prompt_template_key'] ?? self::DEFAULT_PROMPT_KEY,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJson(string $rawContent): ?array
    {
        try {
            $decoded = json_decode($rawContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
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
            type: AiPromptTemplate::TYPE_EDITORIAL_REFINER,
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
