<?php

namespace App\Modules\Ai\Services;

use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\ContentBriefs\Data\GeneratedContentBriefData;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Modules\ContentBriefs\Services\GenerateContentBriefFromTopicService;
use RuntimeException;

class ContentBriefWorkflow
{
    public function __construct(
        private readonly AiJobRepository $jobs,
        private readonly ContentBriefRepository $briefs,
        private readonly GenerateContentBriefFromTopicService $generateBrief,
    ) {}

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

        return $this->generateBrief->handle(
            topic: $topic,
            aiJobId: (int) $job->id,
            promptTemplateKey: $promptTemplateKey,
        );
    }
}
