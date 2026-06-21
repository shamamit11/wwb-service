<?php

namespace App\AI\Agents;

use App\AI\Contracts\ContentAgentInterface;
use App\AI\DTO\AgentErrorData;
use App\AI\DTO\AgentInput;
use App\AI\DTO\AgentResult;
use App\AI\DTO\TopicDiscoveryInput;
use App\AI\DTO\TopicDiscoveryResult;
use App\AI\DTO\TopicSuggestionData;
use App\AI\Support\DecodesJsonResponse;
use App\AI\Tools\CheckDuplicateTopicTool;
use App\AI\Tools\SaveTopicIdeaTool;
use App\Infrastructure\Ai\Contracts\AiClient;
use App\Models\AiPromptTemplate;
use App\Models\ContentTopic;
use App\Modules\Ai\Data\CreateAiGenerationStepData;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Repositories\AiPromptTemplateRepository;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\Ai\Services\RecordAiUsageService;
use App\Modules\Ai\Services\RenderAiPromptTemplateService;
use App\Modules\Ai\Services\TrackAiJobService;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class TopicDiscoveryAgent implements ContentAgentInterface
{
    use DecodesJsonResponse;

    private const DEFAULT_PROMPT_KEY = AiPromptTemplate::KEY_TOPIC_STANDARD;

    public function __construct(
        private readonly AiClient $aiClient,
        private readonly AiPromptTemplateRepository $promptTemplates,
        private readonly AiJobRepository $jobs,
        private readonly RenderAiPromptTemplateService $renderPrompt,
        private readonly TrackAiJobService $trackAiJob,
        private readonly RecordAiUsageService $recordAiUsage,
        private readonly CheckDuplicateTopicTool $checkDuplicateTopic,
        private readonly SaveTopicIdeaTool $saveTopicIdea,
    ) {}

    public function name(): string
    {
        return 'TopicDiscoveryAgent';
    }

    public function run(AgentInput $input): AgentResult
    {
        if (! $input instanceof TopicDiscoveryInput) {
            throw new RuntimeException('TopicDiscoveryAgent requires a TopicDiscoveryInput instance.');
        }

        $job = $this->resolveOrCreateJob($input);

        $step = $this->trackAiJob->createStep(new CreateAiGenerationStepData(
            aiJobId: (int) $job->id,
            agentName: $this->name(),
            inputPayload: $this->buildJobInputPayload($input),
        ));
        $step = $this->trackAiJob->startStep($step);

        try {
            $this->guardCluster($input->cluster);
            $promptTemplate = $this->resolvePromptTemplate($input);
            $renderedPrompt = $this->renderPrompt->render($promptTemplate, $this->buildPromptVariables($input));

            if ($renderedPrompt->missingVariables !== []) {
                throw new RuntimeException('Prompt template is missing required variables: '.implode(', ', $renderedPrompt->missingVariables));
            }

            $response = $this->aiClient->generateText($input->toGenerateTextRequest(
                systemPrompt: $renderedPrompt->systemPrompt,
                prompt: $renderedPrompt->userPrompt,
            ));

            $parsedResponse = $this->parseResponse(
                rawContent: $response->content,
                cluster: $input->cluster,
                targetCount: $input->targetCount,
            );

            [$savedTopics, $skippedDuplicates] = $this->persistTopics($parsedResponse, $input);

            $usagePayload = $response->usage->toArray();
            $outputPayload = [
                'topics' => array_map(
                    static fn (TopicSuggestionData $topic): array => $topic->toArray(),
                    $parsedResponse->topics,
                ),
                'saved_topic_ids' => array_map(
                    static fn (ContentTopic $topic): int => (int) $topic->id,
                    $savedTopics,
                ),
                'skipped_duplicates' => $skippedDuplicates,
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
                    'saved_topic_ids' => array_map(
                        static fn (ContentTopic $topic): int => (int) $topic->id,
                        $savedTopics,
                    ),
                    'skipped_duplicates' => $skippedDuplicates,
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

    private function guardCluster(string $cluster): void
    {
        if (in_array($cluster, ContentTopic::CLUSTERS, true)) {
            return;
        }

        throw new RuntimeException("Unsupported content cluster [{$cluster}] for topic discovery.");
    }

    private function resolvePromptTemplate(TopicDiscoveryInput $input): AiPromptTemplate
    {
        $promptKey = $input->metadata['prompt_template_key'] ?? self::DEFAULT_PROMPT_KEY;
        $promptKey = is_string($promptKey) && $promptKey !== '' ? $promptKey : self::DEFAULT_PROMPT_KEY;

        $template = $this->promptTemplates->findByKey($promptKey)
            ?? $this->promptTemplates->findActiveByType(AiPromptTemplate::TYPE_TOPIC_DISCOVERY);

        if (! $template instanceof AiPromptTemplate || ! $template->activeVersion) {
            throw new RuntimeException('No active topic discovery prompt template is configured.');
        }

        return $template;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPromptVariables(TopicDiscoveryInput $input): array
    {
        return [
            'cluster' => $input->cluster,
            'target_count' => max(1, $input->targetCount),
            'audience' => $input->audience,
            'existing_topics' => array_values($input->existingTopics),
            'knowledge_context' => array_values($input->knowledgeContext),
        ];
    }

    private function parseResponse(string $rawContent, string $cluster, int $targetCount): TopicDiscoveryResult
    {
        $decoded = $this->decodeJson($rawContent);

        if (! is_array($decoded)) {
            throw new RuntimeException('Topic discovery response was not valid JSON.');
        }

        $topicPayloads = $decoded['topics'] ?? null;

        if (! is_array($topicPayloads)) {
            throw new RuntimeException('Topic discovery response did not include a topics array.');
        }

        $topics = [];

        foreach (array_slice($topicPayloads, 0, max(1, $targetCount)) as $topicPayload) {
            if (! is_array($topicPayload)) {
                continue;
            }

            $title = $this->normalizeString($topicPayload['title'] ?? null);

            if ($title === null) {
                continue;
            }

            $topics[] = new TopicSuggestionData(
                title: $title,
                slug: (string) Str::slug($this->normalizeString($topicPayload['slug'] ?? null) ?? $title),
                cluster: $cluster,
                primaryKeyword: $this->normalizeString($topicPayload['primary_keyword'] ?? null),
                secondaryKeywords: $this->normalizeStringList($topicPayload['secondary_keywords'] ?? []),
                searchIntent: $this->normalizeString($topicPayload['search_intent'] ?? null),
                priorityScore: $this->resolvePriorityScore($topicPayload),
                scoreBreakdown: $this->resolveScoreBreakdown($topicPayload),
                difficultyNote: $this->normalizeString($topicPayload['difficulty_note'] ?? null),
                summary: $this->normalizeString($topicPayload['summary'] ?? null),
            );
        }

        if ($topics === []) {
            throw new RuntimeException('Topic discovery response did not contain any valid topic suggestions.');
        }

        return new TopicDiscoveryResult($topics);
    }

    /**
     * @param  array<string, mixed>  $topicPayload
     */
    private function resolvePriorityScore(array $topicPayload): ?string
    {
        $explicit = $this->normalizeDecimal($topicPayload['priority_score'] ?? null);

        if ($explicit !== null) {
            return $explicit;
        }

        $breakdown = $this->resolveScoreBreakdown($topicPayload);

        if ($breakdown === null) {
            return null;
        }

        $total = array_sum(array_map(static fn (mixed $value): float => (float) $value, $breakdown));

        return number_format($total, 2, '.', '');
    }

    /**
     * @param  array<string, mixed>  $topicPayload
     * @return array<string, float>|null
     */
    private function resolveScoreBreakdown(array $topicPayload): ?array
    {
        $map = [
            'trend_score' => 35.0,
            'knowledge_base_fit' => 20.0,
            'business_value' => 20.0,
            'originality_gap' => 15.0,
            'execution_confidence' => 10.0,
        ];

        $scores = [];

        foreach ($map as $key => $max) {
            $value = $topicPayload[$key] ?? null;

            if (! is_numeric($value)) {
                return null;
            }

            $scores[$key] = max(0.0, min($max, (float) $value));
        }

        return $scores;
    }

    /**
     * @return array{0:list<ContentTopic>,1:list<array{title:string, matches:list<string>}>}
     */
    private function persistTopics(TopicDiscoveryResult $result, TopicDiscoveryInput $input): array
    {
        $savedTopics = [];
        $skippedDuplicates = [];
        $seenKeys = [];

        foreach ($result->topics as $topic) {
            $dedupeKey = mb_strtolower($topic->title.'|'.($topic->primaryKeyword ?? ''));

            if (isset($seenKeys[$dedupeKey])) {
                $skippedDuplicates[] = [
                    'title' => $topic->title,
                    'matches' => ['current_run'],
                ];

                continue;
            }

            $seenKeys[$dedupeKey] = true;

            $duplicateCheck = $this->checkDuplicateTopic->check(
                title: $topic->title,
                cluster: $topic->cluster,
                primaryKeyword: $topic->primaryKeyword,
                slug: $topic->slug,
            );

            if ($duplicateCheck['is_duplicate']) {
                $skippedDuplicates[] = [
                    'title' => $topic->title,
                    'matches' => $duplicateCheck['matches'],
                ];

                continue;
            }

            $savedTopics[] = $this->saveTopicIdea->save($topic, $input->audience);
        }

        return [$savedTopics, $skippedDuplicates];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildJobInputPayload(TopicDiscoveryInput $input): array
    {
        return [
            'cluster' => $input->cluster,
            'target_count' => max(1, $input->targetCount),
            'audience' => $input->audience,
            'existing_topics' => array_values($input->existingTopics),
            'knowledge_context' => array_values($input->knowledgeContext),
            'prompt_template_key' => $input->metadata['prompt_template_key'] ?? self::DEFAULT_PROMPT_KEY,
        ];
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
     */
    private function normalizeDecimal(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    private function resolveProvider(TopicDiscoveryInput $input): ?string
    {
        return $input->provider
            ?? config('ai.service.default_provider')
            ?? config('ai.default');
    }

    private function resolveModel(TopicDiscoveryInput $input): ?string
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

    private function resolveOrCreateJob(TopicDiscoveryInput $input): \App\Models\AiJob
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
            type: AiPromptTemplate::TYPE_TOPIC_DISCOVERY,
            status: \App\Models\AiJob::STATUS_PENDING,
            entityType: 'content_topic_batch',
            provider: $this->resolveProvider($input),
            model: $this->resolveModel($input),
            inputPayload: $this->buildJobInputPayload($input),
        ));

        $job = $this->trackAiJob->queueJob($job);

        return $this->trackAiJob->startJob($job);
    }
}
