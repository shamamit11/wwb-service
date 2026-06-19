<?php

namespace App\AI\Agents;

use App\AI\Contracts\ContentAgentInterface;
use App\AI\DTO\AgentErrorData;
use App\AI\DTO\AgentInput;
use App\AI\DTO\AgentResult;
use App\AI\DTO\PostRewriteInput;
use App\AI\DTO\PostRewriteResult;
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

class DraftRewriteAgent implements ContentAgentInterface
{
    use DecodesJsonResponse;

    private const DEFAULT_PROMPT_KEY = 'post_rewrite_default';

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
        return 'DraftRewriteAgent';
    }

    public function run(AgentInput $input): AgentResult
    {
        if (! $input instanceof PostRewriteInput) {
            throw new RuntimeException('DraftRewriteAgent requires a PostRewriteInput instance.');
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
                'scope' => $input->scope,
                'target_block_ids' => $input->targetBlockIds,
                'block_count' => count($parsedResponse->contentBlocks),
                'title' => $parsedResponse->title,
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

    private function resolvePromptTemplate(PostRewriteInput $input): AiPromptTemplate
    {
        $promptKey = $input->metadata['prompt_template_key'] ?? self::DEFAULT_PROMPT_KEY;
        $promptKey = is_string($promptKey) && $promptKey !== '' ? $promptKey : self::DEFAULT_PROMPT_KEY;

        $template = $this->promptTemplates->findByKey($promptKey)
            ?? $this->promptTemplates->findActiveByType(AiPromptTemplate::TYPE_EDITOR);

        if (! $template instanceof AiPromptTemplate || ! $template->activeVersion) {
            throw new RuntimeException('No active draft rewrite prompt template is configured.');
        }

        return $template;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPromptVariables(PostRewriteInput $input): array
    {
        return [
            'scope' => $input->scope,
            'instructions' => $input->instructions,
            'post_title' => $input->postTitle,
            'post_excerpt' => $input->postExcerpt,
            'post_slug' => $input->postSlug,
            'primary_keyword' => $input->primaryKeyword,
            'secondary_keywords' => $input->secondaryKeywords,
            'search_intent' => $input->searchIntent,
            'existing_markdown_body' => $input->existingMarkdownBody,
            'existing_content_blocks' => $input->existingContentBlocks,
            'target_block_ids' => $input->targetBlockIds,
            'target_blocks' => $input->targetBlocks,
            'knowledge_context' => $input->knowledgeBaseContext,
            'brief_outline' => $input->briefOutline,
            'brief_headings' => $input->briefHeadings,
            'faq_suggestions' => $input->faqSuggestions,
        ];
    }

    private function parseResponse(string $rawContent, PostRewriteInput $input): PostRewriteResult
    {
        $decoded = $this->decodeJson($rawContent);

        if (! is_array($decoded)) {
            throw new RuntimeException('Draft rewrite response was not valid JSON.');
        }

        $contentBlocks = $this->normalizeContentBlocks($decoded['content_blocks'] ?? []);

        if ($contentBlocks === []) {
            throw new RuntimeException('Draft rewrite response did not include any valid content_blocks.');
        }

        return new PostRewriteResult(
            title: $this->normalizeString($decoded['title'] ?? null),
            slug: $this->normalizeString($decoded['slug'] ?? null),
            excerpt: $this->normalizeString($decoded['excerpt'] ?? null),
            contentBlocks: $contentBlocks,
            seoTitle: $this->normalizeString($decoded['seo_title'] ?? null),
            metaDescription: $this->normalizeString($decoded['meta_description'] ?? null),
            suggestedTags: $this->normalizeStringList($decoded['suggested_tags'] ?? []),
            imagePlacementNotes: $this->normalizeStringList($decoded['image_placement_notes'] ?? []),
            altTextSuggestions: $this->normalizeStringList($decoded['alt_text_suggestions'] ?? []),
            scope: $input->scope,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildJobInputPayload(PostRewriteInput $input): array
    {
        return [
            'post_id' => $input->postId,
            'scope' => $input->scope,
            'target_block_ids' => $input->targetBlockIds,
            'instructions' => $input->instructions,
            'prompt_template_key' => $input->metadata['prompt_template_key'] ?? self::DEFAULT_PROMPT_KEY,
        ];
    }

    /**
     * @param  mixed  $value
     * @return list<array<string, mixed>>
     */
    private function normalizeContentBlocks(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $blocks = [];

        foreach (array_values($value) as $index => $blockPayload) {
            if (! is_array($blockPayload)) {
                continue;
            }

            $blockType = $this->normalizeString($blockPayload['block_type'] ?? $blockPayload['type'] ?? null);
            $content = $blockPayload['content'] ?? null;

            if ($blockType === null || ! is_array($content)) {
                continue;
            }

            $sortOrder = $blockPayload['sort_order'] ?? ($index + 1);
            $sortOrder = is_numeric($sortOrder) ? max(1, (int) $sortOrder) : ($index + 1);

            $blocks[] = [
                'block_type' => $blockType,
                'sort_order' => $sortOrder,
                'content' => $content,
            ];
        }

        usort($blocks, static fn (array $left, array $right): int => $left['sort_order'] <=> $right['sort_order']);

        return array_values(array_map(
            static fn (array $block, int $index): array => [
                'block_type' => $block['block_type'],
                'sort_order' => $index + 1,
                'content' => $block['content'],
            ],
            $blocks,
            array_keys($blocks),
        ));
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

    private function resolveProvider(PostRewriteInput $input): ?string
    {
        return $input->provider
            ?? config('ai.service.default_provider')
            ?? config('ai.default');
    }

    private function resolveModel(PostRewriteInput $input): ?string
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

    private function resolveOrCreateJob(PostRewriteInput $input): \App\Models\AiJob
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
            type: AiPromptTemplate::TYPE_EDITOR,
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
