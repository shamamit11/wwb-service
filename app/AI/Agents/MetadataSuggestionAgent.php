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

class MetadataSuggestionAgent implements ContentAgentInterface
{
    use DecodesJsonResponse;

    private const DEFAULT_PROMPT_KEY = 'post_metadata_suggestion_default';

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
            $promptTemplate = $this->resolvePromptTemplate($input);
            $renderedPrompt = $this->renderPrompt->render($promptTemplate, $this->buildPromptVariables($input));

            if ($renderedPrompt->missingVariables !== []) {
                throw new RuntimeException('Prompt template is missing required variables: '.implode(', ', $renderedPrompt->missingVariables));
            }

            $response = $this->aiClient->generateText($input->toGenerateTextRequest(
                systemPrompt: $renderedPrompt->systemPrompt,
                prompt: $renderedPrompt->userPrompt,
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

    private function resolvePromptTemplate(PostMetadataSuggestionInput $input): AiPromptTemplate
    {
        $promptKey = $input->metadata['prompt_template_key'] ?? self::DEFAULT_PROMPT_KEY;
        $promptKey = is_string($promptKey) && $promptKey !== '' ? $promptKey : self::DEFAULT_PROMPT_KEY;

        $template = $this->promptTemplates->findByKey($promptKey)
            ?? $this->promptTemplates->findActiveByType(AiPromptTemplate::TYPE_SEO_OPTIMIZER);

        if (! $template instanceof AiPromptTemplate || ! $template->activeVersion) {
            throw new RuntimeException('No active metadata suggestion prompt template is configured.');
        }

        return $template;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPromptVariables(PostMetadataSuggestionInput $input): array
    {
        return [
            'post_title' => $input->postTitle,
            'post_slug' => $input->postSlug,
            'post_excerpt' => $input->postExcerpt,
            'post_status' => $input->postStatus,
            'primary_keyword' => $input->primaryKeyword,
            'secondary_keywords' => $input->secondaryKeywords,
            'existing_focus_keyword' => $input->existingFocusKeyword,
            'existing_meta_title' => $input->existingMetaTitle,
            'existing_meta_description' => $input->existingMetaDescription,
            'existing_markdown_body' => $input->existingMarkdownBody,
            'existing_tags' => $input->existingTags,
            'knowledge_context' => $input->knowledgeBaseContext,
            'brief_outline' => $input->briefOutline,
            'brief_headings' => $input->briefHeadings,
            'instructions' => $input->instructions,
        ];
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
            'prompt_template_key' => $input->metadata['prompt_template_key'] ?? self::DEFAULT_PROMPT_KEY,
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

    private function resolveOrCreateJob(PostMetadataSuggestionInput $input): \App\Models\AiJob
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
            type: AiPromptTemplate::TYPE_SEO_OPTIMIZER,
            status: \App\Models\AiJob::STATUS_PENDING,
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
