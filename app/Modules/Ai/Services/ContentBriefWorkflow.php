<?php

namespace App\Modules\Ai\Services;

use App\Jobs\AI\GenerateContentBriefJob;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\ContentBriefs\Exceptions\ContentBriefGenerationNotAllowedException;
use App\Modules\ContentBriefs\Data\GeneratedContentBriefData;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Modules\ContentBriefs\Services\ContinueContentBriefToDraftService;
use App\Modules\ContentBriefs\Services\GenerateContentBriefFromTopicService;
use RuntimeException;

class ContentBriefWorkflow
{
    public function __construct(
        private readonly AiJobRepository $jobs,
        private readonly ContentBriefRepository $briefs,
        private readonly GenerateContentBriefFromTopicService $generateBrief,
        private readonly ContinueContentBriefToDraftService $continueBriefToDraft,
    ) {}

    public function queue(
        ContentTopic $topic,
        ?string $promptTemplateKey = null,
        ?int $retryOfAiJobId = null,
        int $attempts = 1,
        bool $autoContinueToDraft = false,
    ): ?AiJob
    {
        if (! $topic->isApproved()) {
            throw new ContentBriefGenerationNotAllowedException(
                topicStatus: $topic->status,
                message: "Content brief can only be generated from approved topics. Current status is [{$topic->status}].",
            );
        }

        if ($this->briefs->findByTopicId((int) $topic->id) instanceof ContentBrief) {
            return null;
        }

        $activeJob = AiJob::query()
            ->where('type', AiPromptTemplate::TYPE_CONTENT_BRIEF)
            ->where('entity_type', 'content_topic')
            ->where('entity_id', (int) $topic->id)
            ->whereIn('status', [
                AiJob::STATUS_PENDING,
                AiJob::STATUS_QUEUED,
                AiJob::STATUS_PROCESSING,
            ])
            ->latest('id')
            ->first();

        if ($activeJob instanceof AiJob) {
            if ($autoContinueToDraft) {
                $payload = is_array($activeJob->input_payload) ? $activeJob->input_payload : [];

                if (($payload['auto_continue_to_draft'] ?? false) !== true) {
                    $payload['auto_continue_to_draft'] = true;
                    $activeJob->update(['input_payload' => $payload]);
                }
            }

            return $activeJob->loadCount('steps');
        }

        $job = $this->jobs->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_CONTENT_BRIEF,
            status: AiJob::STATUS_QUEUED,
            entityType: 'content_topic',
            entityId: (int) $topic->id,
            inputPayload: [
                'content_topic_id' => (int) $topic->id,
                'prompt_template_key' => $promptTemplateKey,
                'auto_continue_to_draft' => $autoContinueToDraft,
            ],
            attempts: max(1, $attempts),
            retryOfAiJobId: $retryOfAiJobId,
        ));

        GenerateContentBriefJob::dispatch((int) $job->id);

        return $job;
    }

    public function generate(ContentTopic $topic, ?string $promptTemplateKey = null): GeneratedContentBriefData
    {
        $existing = $this->briefs->findByTopicId((int) $topic->id);

        if ($existing instanceof ContentBrief) {
            return new GeneratedContentBriefData($existing, false);
        }

        $job = $this->jobs->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_CONTENT_BRIEF,
            status: AiJob::STATUS_PENDING,
            entityType: 'content_topic',
            entityId: (int) $topic->id,
            inputPayload: [
                'content_topic_id' => (int) $topic->id,
                'prompt_template_key' => $promptTemplateKey,
            ],
        ));

        return $this->generateBrief->handle(
            topic: $topic,
            aiJobId: (int) $job->id,
            promptTemplateKey: $promptTemplateKey,
        );
    }

    public function runQueued(int $aiJobId): GeneratedContentBriefData
    {
        $job = $this->jobs->findById($aiJobId);

        if (! $job instanceof AiJob) {
            throw new RuntimeException("AI job [{$aiJobId}] could not be found.");
        }

        $payload = is_array($job->input_payload) ? $job->input_payload : [];
        $topicId = $payload['content_topic_id'] ?? null;

        if (! is_int($topicId) && ! (is_string($topicId) && ctype_digit($topicId))) {
            throw new RuntimeException("Queued content brief job [{$aiJobId}] is missing a valid [content_topic_id] value.");
        }

        $topic = ContentTopic::query()->find((int) $topicId);

        if (! $topic instanceof ContentTopic) {
            throw new RuntimeException("Content topic [{$topicId}] could not be found.");
        }

        $promptTemplateKey = is_string($payload['prompt_template_key'] ?? null) && $payload['prompt_template_key'] !== ''
            ? $payload['prompt_template_key']
            : null;

        $result = $this->generateBrief->handle(
            topic: $topic,
            aiJobId: (int) $job->id,
            promptTemplateKey: $promptTemplateKey,
        );

        if (($payload['auto_continue_to_draft'] ?? false) === true) {
            $this->continueBriefToDraft->handle($result->brief);
        }

        return $result;
    }
}
