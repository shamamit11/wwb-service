<?php

namespace App\AI\Agents;

use App\AI\Contracts\ContentAgentInterface;
use App\AI\DTO\AgentErrorData;
use App\AI\DTO\AgentInput;
use App\AI\DTO\AgentResult;
use App\AI\DTO\ContentBriefInput;
use App\AI\DTO\ContentBriefResult;
use App\AI\Support\DecodesJsonResponse;
use App\AI\Tools\FindInternalLinksTool;
use App\AI\Tools\SaveContentBriefTool;
use App\AI\Tools\SearchExistingPostsTool;
use App\Infrastructure\Ai\Contracts\AiClient;
use App\Models\AiPromptTemplate;
use App\Modules\Ai\Data\CreateAiGenerationStepData;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Repositories\AiPromptTemplateRepository;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\Ai\Services\RecordAiUsageService;
use App\Modules\Ai\Services\RenderAiPromptTemplateService;
use App\Modules\Ai\Services\TrackAiJobService;
use RuntimeException;
use Throwable;

class ContentBriefAgent implements ContentAgentInterface
{
    use DecodesJsonResponse;

    private const DEFAULT_PROMPT_KEY = 'content_brief_default';

    public function __construct(
        private readonly AiClient $aiClient,
        private readonly AiPromptTemplateRepository $promptTemplates,
        private readonly AiJobRepository $jobs,
        private readonly RenderAiPromptTemplateService $renderPrompt,
        private readonly TrackAiJobService $trackAiJob,
        private readonly RecordAiUsageService $recordAiUsage,
        private readonly SearchExistingPostsTool $searchExistingPosts,
        private readonly FindInternalLinksTool $findInternalLinks,
        private readonly SaveContentBriefTool $saveContentBrief,
    ) {}

    public function name(): string
    {
        return 'ContentBriefAgent';
    }

    public function run(AgentInput $input): AgentResult
    {
        if (! $input instanceof ContentBriefInput) {
            throw new RuntimeException('ContentBriefAgent requires a ContentBriefInput instance.');
        }

        $contextualInput = $this->hydrateContext($input);

        $job = $this->resolveOrCreateJob($contextualInput);

        $step = $this->trackAiJob->createStep(new CreateAiGenerationStepData(
            aiJobId: (int) $job->id,
            agentName: $this->name(),
            inputPayload: $this->buildJobInputPayload($contextualInput),
        ));
        $step = $this->trackAiJob->startStep($step);

        try {
            $promptTemplate = $this->resolvePromptTemplate($contextualInput);
            $renderedPrompt = $this->renderPrompt->render($promptTemplate, $this->buildPromptVariables($contextualInput));

            if ($renderedPrompt->missingVariables !== []) {
                throw new RuntimeException('Prompt template is missing required variables: '.implode(', ', $renderedPrompt->missingVariables));
            }

            $response = $this->aiClient->generateText($contextualInput->toGenerateTextRequest(
                systemPrompt: $renderedPrompt->systemPrompt,
                prompt: $renderedPrompt->userPrompt,
            ));

            $parsedResponse = $this->parseResponse($response->content, $contextualInput);
            $brief = $this->saveContentBrief->save(
                contentTopicId: $contextualInput->contentTopicId,
                primaryKeyword: $contextualInput->primaryKeyword,
                secondaryKeywords: $contextualInput->secondaryKeywords,
                searchIntent: $contextualInput->searchIntent,
                result: $parsedResponse,
            );

            $usagePayload = $response->usage->toArray();
            $outputPayload = [
                'brief_id' => (int) $brief->id,
                'recommended_title' => $parsedResponse->recommendedTitle,
                'slug' => $brief->slug,
                'outline' => $parsedResponse->outline,
                'heading_structure' => $parsedResponse->headingStructure,
                'faq_suggestions' => $parsedResponse->faqSuggestions,
                'internal_link_suggestions' => $parsedResponse->internalLinkSuggestions,
                'image_suggestions' => $parsedResponse->imageSuggestions(),
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
                    'brief_id' => (int) $brief->id,
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
                ],
            );
        }
    }

    private function hydrateContext(ContentBriefInput $input): ContentBriefInput
    {
        $excerpt = $input->editorialIntent;

        $existingPostContext = $input->existingPostContext !== []
            ? $input->existingPostContext
            : $this->searchExistingPosts->search(
                title: $input->topicTitle,
                primaryKeyword: $input->primaryKeyword,
                secondaryKeywords: $input->secondaryKeywords,
                excerpt: $excerpt,
            );

        $internalLinkContext = $input->internalLinkContext !== []
            ? $input->internalLinkContext
            : $this->findInternalLinks->suggest(
                title: $input->topicTitle,
                primaryKeyword: $input->primaryKeyword,
                secondaryKeywords: $input->secondaryKeywords,
                excerpt: $excerpt,
            );

        return new ContentBriefInput(
            contentTopicId: $input->contentTopicId,
            topicTitle: $input->topicTitle,
            cluster: $input->cluster,
            primaryKeyword: $input->primaryKeyword,
            secondaryKeywords: $input->secondaryKeywords,
            searchIntent: $input->searchIntent,
            knowledgeBaseContext: $input->knowledgeBaseContext,
            existingPostContext: $existingPostContext,
            internalLinkContext: $internalLinkContext,
            editorialIntent: $input->editorialIntent,
            provider: $input->provider,
            model: $input->model,
            timeoutSeconds: $input->timeoutSeconds,
            retryTimes: $input->retryTimes,
            retrySleepMilliseconds: $input->retrySleepMilliseconds,
            metadata: $input->metadata,
        );
    }

    private function resolvePromptTemplate(ContentBriefInput $input): AiPromptTemplate
    {
        $promptKey = $input->metadata['prompt_template_key'] ?? self::DEFAULT_PROMPT_KEY;
        $promptKey = is_string($promptKey) && $promptKey !== '' ? $promptKey : self::DEFAULT_PROMPT_KEY;

        $template = $this->promptTemplates->findByKey($promptKey)
            ?? $this->promptTemplates->findActiveByType(AiPromptTemplate::TYPE_CONTENT_BRIEF);

        if (! $template instanceof AiPromptTemplate || ! $template->activeVersion) {
            throw new RuntimeException('No active content brief prompt template is configured.');
        }

        return $template;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPromptVariables(ContentBriefInput $input): array
    {
        return [
            'topic_title' => $input->topicTitle,
            'cluster' => $input->cluster,
            'primary_keyword' => $input->primaryKeyword,
            'secondary_keywords' => $input->secondaryKeywords,
            'search_intent' => $input->searchIntent,
            'knowledge_context' => $input->knowledgeBaseContext,
            'existing_post_context' => $input->existingPostContext,
            'internal_link_context' => $input->internalLinkContext,
            'editorial_intent' => $input->editorialIntent,
        ];
    }

    private function parseResponse(string $rawContent, ContentBriefInput $input): ContentBriefResult
    {
        $decoded = $this->decodeJson($rawContent);

        if (! is_array($decoded)) {
            throw new RuntimeException('Content brief response was not valid JSON.');
        }

        $recommendedTitle = $this->normalizeString($decoded['recommended_title'] ?? null) ?? $input->topicTitle;
        $slug = $this->normalizeString($decoded['slug'] ?? null) ?? $recommendedTitle;
        $outline = $this->normalizeArrayList($decoded['outline'] ?? []);
        $headingStructure = $this->normalizeStringList($decoded['heading_structure'] ?? []);

        if ($outline === [] || $headingStructure === []) {
            throw new RuntimeException('Content brief response did not include the required structured outline fields.');
        }

        return new ContentBriefResult(
            recommendedTitle: $recommendedTitle,
            slug: (string) \Illuminate\Support\Str::slug($slug),
            metaTitle: $this->normalizeString($decoded['meta_title'] ?? null),
            metaDescription: $this->normalizeString($decoded['meta_description'] ?? null),
            introAngle: $this->normalizeString($decoded['intro_angle'] ?? null),
            targetAudience: $this->normalizeString($decoded['target_audience'] ?? null),
            outline: $outline,
            headingStructure: $headingStructure,
            faqSuggestions: $this->normalizeArrayList($decoded['faq_suggestions'] ?? []),
            internalLinkSuggestions: $this->normalizeArrayList($decoded['internal_link_suggestions'] ?? []),
            imageIdeas: $this->normalizeStringList($decoded['image_ideas'] ?? []),
            altTextSuggestions: $this->normalizeStringList($decoded['alt_text_suggestions'] ?? []),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildJobInputPayload(ContentBriefInput $input): array
    {
        return [
            'content_topic_id' => $input->contentTopicId,
            'topic_title' => $input->topicTitle,
            'cluster' => $input->cluster,
            'primary_keyword' => $input->primaryKeyword,
            'secondary_keywords' => $input->secondaryKeywords,
            'search_intent' => $input->searchIntent,
            'knowledge_context' => $input->knowledgeBaseContext,
            'existing_post_context' => $input->existingPostContext,
            'internal_link_context' => $input->internalLinkContext,
            'editorial_intent' => $input->editorialIntent,
            'prompt_template_key' => $input->metadata['prompt_template_key'] ?? self::DEFAULT_PROMPT_KEY,
        ];
    }

    /**
     * @param  mixed  $value
     */
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
     * @param  mixed  $value
     * @return list<array<string, mixed>>
     */
    private function normalizeArrayList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, static fn (mixed $item): bool => is_array($item)));
    }

    private function resolveProvider(ContentBriefInput $input): ?string
    {
        return $input->provider
            ?? config('ai.service.default_provider')
            ?? config('ai.default');
    }

    private function resolveModel(ContentBriefInput $input): ?string
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

    private function resolveOrCreateJob(ContentBriefInput $input): \App\Models\AiJob
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
            type: AiPromptTemplate::TYPE_CONTENT_BRIEF,
            status: \App\Models\AiJob::STATUS_PENDING,
            entityType: 'content_topic',
            entityId: $input->contentTopicId,
            provider: $this->resolveProvider($input),
            model: $this->resolveModel($input),
            inputPayload: $this->buildJobInputPayload($input),
        ));

        $job = $this->trackAiJob->queueJob($job);

        return $this->trackAiJob->startJob($job);
    }
}
