<?php

namespace App\Modules\Ai\Services;

use App\AI\DTO\AgentResult;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Models\Post;
use App\Modules\Ai\Data\DiscoverContentTopicsData;
use App\Modules\Ai\Data\QueueBlogDraftGenerationData;
use App\Modules\Ai\Data\QueuePostRewriteData;
use App\Modules\ContentBriefs\Data\GeneratedContentBriefData;
use RuntimeException;

class AiWorkflowOrchestrator
{
    public function __construct(
        private readonly TopicDiscoveryWorkflow $topicDiscovery,
        private readonly ContentBriefWorkflow $contentBriefs,
        private readonly DraftGenerationWorkflow $drafts,
        private readonly DraftRewriteWorkflow $rewrites,
    ) {}

    public function dispatchTopicDiscovery(DiscoverContentTopicsData $data, ?int $retryOfAiJobId = null, int $attempts = 1): AiJob
    {
        return $this->topicDiscovery->dispatch($data, $retryOfAiJobId, $attempts);
    }

    public function runTopicDiscovery(DiscoverContentTopicsData $data, ?int $aiJobId = null): AgentResult
    {
        return $this->topicDiscovery->run($data, $aiJobId);
    }

    public function runQueuedTopicDiscovery(int $aiJobId): AgentResult
    {
        return $this->topicDiscovery->runQueued($aiJobId);
    }

    public function generateContentBrief(ContentTopic $topic, ?string $promptTemplateKey = null): GeneratedContentBriefData
    {
        return $this->contentBriefs->generate($topic, $promptTemplateKey);
    }

    public function runQueuedContentBrief(int $aiJobId): GeneratedContentBriefData
    {
        return $this->contentBriefs->runQueued($aiJobId);
    }

    public function queueDraftGeneration(ContentBrief $brief, QueueBlogDraftGenerationData $data, ?int $retryOfAiJobId = null, int $attempts = 1): AiJob
    {
        return $this->drafts->queue($brief, $data, $retryOfAiJobId, $attempts);
    }

    public function runQueuedDraftGeneration(int $aiJobId): void
    {
        $this->drafts->runQueued($aiJobId);
    }

    public function queuePostRewrite(Post $post, QueuePostRewriteData $data, ?int $retryOfAiJobId = null, int $attempts = 1): AiJob
    {
        return $this->rewrites->queue($post, $data, $retryOfAiJobId, $attempts);
    }

    public function runQueuedPostRewrite(int $aiJobId): void
    {
        $this->rewrites->runQueued($aiJobId);
    }

    public function retry(AiJob $job): AiJob
    {
        if (! $job->canRetry()) {
            throw new \App\Modules\Ai\Exceptions\AiJobRetryNotAllowedException($job->status);
        }

        return match ($job->type) {
            AiPromptTemplate::TYPE_TOPIC_DISCOVERY => $this->retryTopicDiscovery($job),
            AiPromptTemplate::TYPE_CONTENT_BRIEF => $this->retryContentBrief($job),
            AiPromptTemplate::TYPE_BLOG_WRITER => $this->retryBlogWriter($job),
            AiPromptTemplate::TYPE_EDITOR => $this->retryEditor($job),
            default => throw new RuntimeException("AI job retry is not supported for type [{$job->type}]."),
        };
    }

    private function retryTopicDiscovery(AiJob $job): AiJob
    {
        $payload = is_array($job->input_payload) ? $job->input_payload : [];
        $cluster = $payload['cluster'] ?? null;

        if (! is_string($cluster) || $cluster === '') {
            throw new RuntimeException("Retry topic discovery job [{$job->id}] is missing a valid [cluster] value.");
        }

        return $this->dispatchTopicDiscovery(new DiscoverContentTopicsData(
            cluster: $cluster,
            count: is_int($payload['count'] ?? null) ? $payload['count'] : (int) ($payload['count'] ?? 10),
            audience: is_string($payload['audience'] ?? null) && $payload['audience'] !== '' ? $payload['audience'] : null,
            promptTemplateKey: is_string($payload['prompt_template_key'] ?? null) && $payload['prompt_template_key'] !== '' ? $payload['prompt_template_key'] : null,
            metadata: is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [],
        ), (int) $job->id, $job->attempts + 1);
    }

    private function retryContentBrief(AiJob $job): AiJob
    {
        $payload = is_array($job->input_payload) ? $job->input_payload : [];
        $topicId = $payload['content_topic_id'] ?? $job->entity_id;

        if (! is_int($topicId) && ! (is_string($topicId) && ctype_digit($topicId))) {
            throw new RuntimeException("Retry content brief job [{$job->id}] is missing a valid [content_topic_id] value.");
        }

        $retry = \App\Models\AiJob::query()->create([
            'type' => AiPromptTemplate::TYPE_CONTENT_BRIEF,
            'status' => AiJob::STATUS_QUEUED,
            'entity_type' => 'content_topic',
            'entity_id' => (int) $topicId,
            'provider' => $job->provider,
            'model' => $job->model,
            'input_payload' => [
                'content_topic_id' => (int) $topicId,
                'prompt_template_key' => is_string($payload['prompt_template_key'] ?? null) ? $payload['prompt_template_key'] : null,
            ],
            'attempts' => $job->attempts + 1,
            'retry_of_ai_job_id' => (int) $job->id,
        ]);

        \App\Jobs\AI\GenerateContentBriefJob::dispatch((int) $retry->id);

        return $retry->refresh()->loadCount('steps');
    }

    private function retryBlogWriter(AiJob $job): AiJob
    {
        $payload = is_array($job->input_payload) ? $job->input_payload : [];
        $briefId = $payload['content_brief_id'] ?? $job->entity_id;

        if (! is_int($briefId) && ! (is_string($briefId) && ctype_digit($briefId))) {
            throw new RuntimeException("Retry blog draft job [{$job->id}] is missing a valid [content_brief_id] value.");
        }

        $brief = ContentBrief::query()->find((int) $briefId);

        if (! $brief instanceof ContentBrief) {
            throw new RuntimeException("Content brief [{$briefId}] could not be found.");
        }

        return $this->queueDraftGeneration($brief, new QueueBlogDraftGenerationData(
            authorUserId: isset($payload['author_user_id']) && $payload['author_user_id'] !== null ? (int) $payload['author_user_id'] : null,
            categoryId: (int) $payload['category_id'],
            templateId: isset($payload['template_id']) && $payload['template_id'] !== null ? (int) $payload['template_id'] : null,
            featuredMediaId: isset($payload['featured_media_id']) && $payload['featured_media_id'] !== null ? (int) $payload['featured_media_id'] : null,
            visibility: is_string($payload['visibility'] ?? null) ? $payload['visibility'] : \App\Models\Post::VISIBILITY_PUBLIC,
            promptTemplateKey: is_string($payload['prompt_template_key'] ?? null) ? $payload['prompt_template_key'] : null,
        ), (int) $job->id, $job->attempts + 1);
    }

    private function retryEditor(AiJob $job): AiJob
    {
        $payload = is_array($job->input_payload) ? $job->input_payload : [];
        $postId = $payload['post_id'] ?? $job->entity_id;

        if (! is_int($postId) && ! (is_string($postId) && ctype_digit($postId))) {
            throw new RuntimeException("Retry post rewrite job [{$job->id}] is missing a valid [post_id] value.");
        }

        $post = Post::query()->find((int) $postId);

        if (! $post instanceof Post) {
            throw new RuntimeException("Post [{$postId}] could not be found.");
        }

        return $this->queuePostRewrite($post, new QueuePostRewriteData(
            scope: $this->rewrites->normalizeScope($payload['scope'] ?? null),
            targetBlockIds: is_array($payload['target_block_ids'] ?? null) ? array_values(array_map(static fn (mixed $id): int => (int) $id, $payload['target_block_ids'])) : [],
            instructions: is_string($payload['instructions'] ?? null) ? $payload['instructions'] : null,
            promptTemplateKey: is_string($payload['prompt_template_key'] ?? null) ? $payload['prompt_template_key'] : null,
        ), (int) $job->id, $job->attempts + 1);
    }
}
