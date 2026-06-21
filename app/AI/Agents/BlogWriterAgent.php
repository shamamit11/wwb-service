<?php

namespace App\AI\Agents;

use App\AI\Contracts\ContentAgentInterface;
use App\AI\DTO\AgentErrorData;
use App\AI\DTO\AgentInput;
use App\AI\DTO\AgentResult;
use App\AI\DTO\BlogDraftInput;
use App\AI\DTO\BlogDraftResult;
use App\AI\Enums\BlogDraftGenerationMode;
use App\AI\Support\DecodesJsonResponse;
use App\AI\Tools\FindInternalLinksTool;
use App\AI\Tools\SavePostDraftTool;
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

class BlogWriterAgent implements ContentAgentInterface
{
    use DecodesJsonResponse;

    private const DEFAULT_PROMPT_KEY = AiPromptTemplate::KEY_BLOG_STANDARD;

    public function __construct(
        private readonly AiClient $aiClient,
        private readonly AiPromptTemplateRepository $promptTemplates,
        private readonly AiJobRepository $jobs,
        private readonly RenderAiPromptTemplateService $renderPrompt,
        private readonly TrackAiJobService $trackAiJob,
        private readonly RecordAiUsageService $recordAiUsage,
        private readonly SearchExistingPostsTool $searchExistingPosts,
        private readonly FindInternalLinksTool $findInternalLinks,
        private readonly SavePostDraftTool $savePostDraft,
    ) {}

    public function name(): string
    {
        return 'BlogWriterAgent';
    }

    public function run(AgentInput $input): AgentResult
    {
        if (! $input instanceof BlogDraftInput) {
            throw new RuntimeException('BlogWriterAgent requires a BlogDraftInput instance.');
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
            $post = $this->savePostDraft->save(
                contentTopicId: $contextualInput->contentTopicId,
                primaryKeyword: $contextualInput->primaryKeyword,
                secondaryKeywords: $contextualInput->secondaryKeywords,
                searchIntent: $contextualInput->searchIntent,
                result: $parsedResponse,
                metadata: $contextualInput->metadata,
            );

            $usagePayload = $response->usage->toArray();
            $outputPayload = [
                'post_id' => (int) $post->id,
                'title' => $parsedResponse->title,
                'slug' => $post->slug,
                'full_article_markdown_length' => mb_strlen($parsedResponse->markdownBody),
                'faq_suggestions' => $parsedResponse->faqSuggestions,
                'suggested_tags' => $parsedResponse->suggestedTags,
                'image_placement_notes' => $parsedResponse->imagePlacementNotes,
                'alt_text_suggestions' => $parsedResponse->altTextSuggestions,
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
                    'post_id' => (int) $post->id,
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

    private function hydrateContext(BlogDraftInput $input): BlogDraftInput
    {
        $existingPostContext = $input->existingPostContext !== []
            ? $input->existingPostContext
            : $this->searchExistingPosts->search(
                title: $input->title,
                primaryKeyword: $input->primaryKeyword,
                secondaryKeywords: $input->secondaryKeywords,
                excerpt: $input->introAngle,
            );

        $internalLinkContext = $input->internalLinkContext !== []
            ? $input->internalLinkContext
            : $this->findInternalLinks->suggest(
                title: $input->title,
                primaryKeyword: $input->primaryKeyword,
                secondaryKeywords: $input->secondaryKeywords,
                excerpt: $input->introAngle,
            );

        return new BlogDraftInput(
            contentTopicId: $input->contentTopicId,
            title: $input->title,
            slug: $input->slug,
            generationMode: $input->generationMode,
            primaryKeyword: $input->primaryKeyword,
            secondaryKeywords: $input->secondaryKeywords,
            searchIntent: $input->searchIntent,
            introAngle: $input->introAngle,
            targetAudience: $input->targetAudience,
            outline: $input->outline,
            headingStructure: $input->headingStructure,
            faqSuggestions: $input->faqSuggestions,
            knowledgeBaseContext: $input->knowledgeBaseContext,
            existingPostContext: $existingPostContext,
            internalLinkContext: $internalLinkContext,
            imageSuggestions: $input->imageSuggestions,
            provider: $input->provider,
            model: $input->model,
            timeoutSeconds: $input->timeoutSeconds,
            retryTimes: $input->retryTimes,
            retrySleepMilliseconds: $input->retrySleepMilliseconds,
            metadata: $input->metadata,
        );
    }

    private function resolvePromptTemplate(BlogDraftInput $input): AiPromptTemplate
    {
        $promptKey = $input->metadata['prompt_template_key'] ?? null;
        $promptKey = is_string($promptKey) && $promptKey !== '' ? $promptKey : null;

        $template = $promptKey !== null
            ? $this->promptTemplates->findByKey($promptKey)
            : $this->resolveModeSpecificPromptTemplate($input);

        $template ??= $this->promptTemplates->findByKey(self::DEFAULT_PROMPT_KEY)
            ?? $this->promptTemplates->findActiveByType(AiPromptTemplate::TYPE_BLOG_WRITER);

        if (! $template instanceof AiPromptTemplate || ! $template->activeVersion) {
            throw new RuntimeException('No active blog writer prompt template is configured.');
        }

        return $template;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPromptVariables(BlogDraftInput $input): array
    {
        $generationMode = $this->resolveGenerationMode($input);

        return [
            'title' => $input->title,
            'slug' => $input->slug,
            'generation_mode' => $generationMode?->value,
            'generation_mode_guidance' => $generationMode?->guidance(),
            'primary_keyword' => $input->primaryKeyword,
            'secondary_keywords' => $input->secondaryKeywords,
            'search_intent' => $input->searchIntent,
            'intro_angle' => $input->introAngle,
            'target_audience' => $input->targetAudience,
            'outline' => $input->outline,
            'heading_structure' => $input->headingStructure,
            'faq_suggestions' => $input->faqSuggestions,
            'knowledge_context' => $input->knowledgeBaseContext,
            'existing_post_context' => $input->existingPostContext,
            'internal_link_context' => $input->internalLinkContext,
            'image_suggestions' => $input->imageSuggestions,
        ];
    }

    private function parseResponse(string $rawContent, BlogDraftInput $input): BlogDraftResult
    {
        $decoded = $this->decodeJson($rawContent);

        if (! is_array($decoded)) {
            throw new RuntimeException('Blog writer response was not valid JSON.');
        }

        $title = $this->normalizeString($decoded['title'] ?? null) ?? $input->title;
        $slug = $this->normalizeString($decoded['slug'] ?? null) ?? $input->slug;
        $markdownBody = $this->normalizeString($decoded['full_article_markdown'] ?? $decoded['markdown_body'] ?? null);

        if ($markdownBody === null) {
            throw new RuntimeException('Blog writer response did not include full_article_markdown.');
        }

        $faqSuggestions = $this->normalizeFaqSuggestions($decoded['faq_suggestions'] ?? []);

        return new BlogDraftResult(
            title: $title,
            slug: (string) \Illuminate\Support\Str::slug($slug),
            markdownBody: $markdownBody,
            shortDescription: $this->normalizeString($decoded['short_description'] ?? null),
            description: $this->normalizeString($decoded['description'] ?? null),
            fullArticleHtml: $this->normalizeString($decoded['full_article_html'] ?? null),
            excerpt: $this->normalizeString($decoded['excerpt'] ?? null),
            seoTitle: $this->normalizeString($decoded['seo_title'] ?? null),
            metaDescription: $this->normalizeString($decoded['meta_description'] ?? null),
            faqSuggestions: $faqSuggestions,
            suggestedTags: $this->normalizeStringList($decoded['suggested_tags'] ?? []),
            imagePlacementNotes: $this->normalizeStringList($decoded['image_placement_notes'] ?? []),
            altTextSuggestions: $this->normalizeStringList($decoded['alt_text_suggestions'] ?? []),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildJobInputPayload(BlogDraftInput $input): array
    {
        return [
            'content_topic_id' => $input->contentTopicId,
            'title' => $input->title,
            'slug' => $input->slug,
            'generation_mode' => $input->generationMode,
            'primary_keyword' => $input->primaryKeyword,
            'secondary_keywords' => $input->secondaryKeywords,
            'search_intent' => $input->searchIntent,
            'intro_angle' => $input->introAngle,
            'target_audience' => $input->targetAudience,
            'outline' => $input->outline,
            'heading_structure' => $input->headingStructure,
            'faq_suggestions' => $input->faqSuggestions,
            'knowledge_context' => $input->knowledgeBaseContext,
            'existing_post_context' => $input->existingPostContext,
            'internal_link_context' => $input->internalLinkContext,
            'image_suggestions' => $input->imageSuggestions,
            'prompt_template_key' => $input->metadata['prompt_template_key'] ?? self::DEFAULT_PROMPT_KEY,
        ];
    }

    private function resolveModeSpecificPromptTemplate(BlogDraftInput $input): ?AiPromptTemplate
    {
        $mode = $this->resolveGenerationMode($input);

        if (! $mode instanceof BlogDraftGenerationMode) {
            return null;
        }

        return $this->promptTemplates->findByKey($mode->promptKey());
    }

    private function resolveGenerationMode(BlogDraftInput $input): ?BlogDraftGenerationMode
    {
        return is_string($input->generationMode) && $input->generationMode !== ''
            ? BlogDraftGenerationMode::tryFrom($input->generationMode)
            : null;
    }

    /**
     * @param  mixed  $value
     * @return list<array{question:string,answer_markdown:string}>
     */
    private function normalizeFaqSuggestions(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $items = [];

        foreach ($value as $faqPayload) {
            if (! is_array($faqPayload)) {
                continue;
            }

            $question = $this->normalizeString($faqPayload['question'] ?? null);
            $answer = $this->normalizeString($faqPayload['answer_markdown'] ?? $faqPayload['answer'] ?? $faqPayload['answer_focus'] ?? null);

            if ($question === null || $answer === null) {
                continue;
            }

            $items[] = [
                'question' => $question,
                'answer_markdown' => $answer,
            ];
        }

        return $items;
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

    private function resolveProvider(BlogDraftInput $input): ?string
    {
        return $input->provider
            ?? config('ai.service.default_provider')
            ?? config('ai.default');
    }

    private function resolveModel(BlogDraftInput $input): ?string
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

    private function resolveOrCreateJob(BlogDraftInput $input): \App\Models\AiJob
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
            type: AiPromptTemplate::TYPE_BLOG_WRITER,
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
