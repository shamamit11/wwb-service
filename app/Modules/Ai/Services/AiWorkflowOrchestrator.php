<?php

namespace App\Modules\Ai\Services;

use App\AI\DTO\AgentResult;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\ContentTopic;
use App\Models\Post;
use App\Modules\Ai\Data\DiscoverContentTopicsData;
use App\Modules\Ai\Data\QueueBlogDraftGenerationData;
use App\Modules\Ai\Data\QueuePostMetadataSuggestionData;
use App\Modules\Ai\Data\QueuePostTitleExcerptRefinementData;
use RuntimeException;

class AiWorkflowOrchestrator
{
    private const JOB_TYPE_POST_METADATA_SUGGESTION = 'post_metadata_suggestion';

    private const JOB_TYPE_POST_TITLE_EXCERPT_REFINEMENT = 'post_title_excerpt_refinement';

    public function __construct(
        private readonly TopicDiscoveryWorkflow $topicDiscovery,
        private readonly DraftGenerationWorkflow $drafts,
        private readonly MetadataSuggestionWorkflow $metadata,
        private readonly TitleExcerptRefinementWorkflow $titleExcerptRefinements,
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

    public function queueDraftGeneration(ContentTopic $topic, QueueBlogDraftGenerationData $data, ?int $retryOfAiJobId = null, int $attempts = 1): AiJob
    {
        return $this->drafts->queue($topic, $data, $retryOfAiJobId, $attempts);
    }

    public function runQueuedDraftGeneration(int $aiJobId): void
    {
        $this->drafts->runQueued($aiJobId);
    }

    public function queuePostMetadataSuggestions(Post $post, QueuePostMetadataSuggestionData $data, ?int $retryOfAiJobId = null, int $attempts = 1): AiJob
    {
        return $this->metadata->queue($post, $data, $retryOfAiJobId, $attempts);
    }

    public function queuePostTitleExcerptRefinement(Post $post, QueuePostTitleExcerptRefinementData $data, ?int $retryOfAiJobId = null, int $attempts = 1): AiJob
    {
        return $this->titleExcerptRefinements->queue($post, $data, $retryOfAiJobId, $attempts);
    }

    public function runQueuedPostMetadataSuggestions(int $aiJobId): void
    {
        $this->metadata->runQueued($aiJobId);
    }

    public function runQueuedPostTitleExcerptRefinement(int $aiJobId): void
    {
        $this->titleExcerptRefinements->runQueued($aiJobId);
    }

    public function retry(AiJob $job): AiJob
    {
        if (! $job->canRetry()) {
            throw new \App\Modules\Ai\Exceptions\AiJobRetryNotAllowedException($job->status);
        }

        return match ($job->type) {
            AiPromptTemplate::TYPE_TOPIC_DISCOVERY => $this->retryTopicDiscovery($job),
            AiPromptTemplate::TYPE_BLOG_WRITER => $this->retryBlogWriter($job),
            self::JOB_TYPE_POST_METADATA_SUGGESTION => $this->retrySeoOptimizer($job),
            self::JOB_TYPE_POST_TITLE_EXCERPT_REFINEMENT => $this->retryEditorialRefiner($job),
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

    private function retryBlogWriter(AiJob $job): AiJob
    {
        $payload = is_array($job->input_payload) ? $job->input_payload : [];
        $topicId = $payload['content_topic_id'] ?? $job->entity_id;

        if (! is_int($topicId) && ! (is_string($topicId) && ctype_digit($topicId))) {
            throw new RuntimeException("Retry blog draft job [{$job->id}] is missing a valid [content_topic_id] value.");
        }

        $topic = ContentTopic::query()->find((int) $topicId);

        if (! $topic instanceof ContentTopic) {
            throw new RuntimeException("Content topic [{$topicId}] could not be found.");
        }

        return $this->queueDraftGeneration($topic, new QueueBlogDraftGenerationData(
            authorUserId: isset($payload['author_user_id']) && $payload['author_user_id'] !== null ? (int) $payload['author_user_id'] : null,
            categoryId: (int) $payload['category_id'],
            featuredMediaId: isset($payload['featured_media_id']) && $payload['featured_media_id'] !== null ? (int) $payload['featured_media_id'] : null,
            visibility: is_string($payload['visibility'] ?? null) ? $payload['visibility'] : \App\Models\Post::VISIBILITY_PUBLIC,
        ), (int) $job->id, $job->attempts + 1);
    }

    private function retrySeoOptimizer(AiJob $job): AiJob
    {
        $payload = is_array($job->input_payload) ? $job->input_payload : [];
        $postId = $payload['post_id'] ?? $job->entity_id;

        if (! is_int($postId) && ! (is_string($postId) && ctype_digit($postId))) {
            throw new RuntimeException("Retry metadata suggestion job [{$job->id}] is missing a valid [post_id] value.");
        }

        $post = Post::query()->find((int) $postId);

        if (! $post instanceof Post) {
            throw new RuntimeException("Post [{$postId}] could not be found.");
        }

        return $this->queuePostMetadataSuggestions($post, new QueuePostMetadataSuggestionData(
            instructions: is_string($payload['instructions'] ?? null) ? $payload['instructions'] : null,
        ), (int) $job->id, $job->attempts + 1);
    }

    private function retryEditorialRefiner(AiJob $job): AiJob
    {
        $payload = is_array($job->input_payload) ? $job->input_payload : [];
        $postId = $payload['post_id'] ?? $job->entity_id;

        if (! is_int($postId) && ! (is_string($postId) && ctype_digit($postId))) {
            throw new RuntimeException("Retry title/excerpt refinement job [{$job->id}] is missing a valid [post_id] value.");
        }

        $post = Post::query()->find((int) $postId);

        if (! $post instanceof Post) {
            throw new RuntimeException("Post [{$postId}] could not be found.");
        }

        return $this->queuePostTitleExcerptRefinement($post, new QueuePostTitleExcerptRefinementData(
            instructions: is_string($payload['instructions'] ?? null) ? $payload['instructions'] : null,
        ), (int) $job->id, $job->attempts + 1);
    }
}
