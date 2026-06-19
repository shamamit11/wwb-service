<?php

namespace App\Modules\Ai\Services;

use App\AI\Agents\TopicDiscoveryAgent;
use App\AI\DTO\AgentResult;
use App\Jobs\AI\DiscoverContentTopicsJob;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\ContentTopic;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Data\DiscoverContentTopicsData;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\ContentTopics\Data\ContentTopicFiltersData;
use App\Modules\ContentTopics\Repositories\ContentTopicRepository;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use App\Modules\KnowledgeBase\Services\KnowledgeContextService;
use RuntimeException;

class TopicDiscoveryWorkflow
{
    public function __construct(
        private readonly AiJobRepository $jobs,
        private readonly TopicDiscoveryAgent $agent,
        private readonly ContentTopicRepository $topics,
        private readonly KnowledgeContextService $knowledgeContext,
    ) {}

    public function dispatch(DiscoverContentTopicsData $data, ?int $retryOfAiJobId = null, int $attempts = 1): AiJob
    {
        $this->guardCluster($data->cluster);

        $job = $this->jobs->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_TOPIC_DISCOVERY,
            status: AiJob::STATUS_QUEUED,
            entityType: 'content_topic_batch',
            inputPayload: [
                'cluster' => $data->cluster,
                'count' => max(1, $data->count),
                'audience' => $data->audience,
                'prompt_template_key' => $data->promptTemplateKey,
                'metadata' => $data->metadata,
            ],
            attempts: max(1, $attempts),
            retryOfAiJobId: $retryOfAiJobId,
        ));

        DiscoverContentTopicsJob::dispatch((int) $job->id);

        return $job;
    }

    public function runQueued(int $aiJobId): AgentResult
    {
        $job = $this->jobs->findById($aiJobId);

        if (! $job instanceof AiJob) {
            throw new RuntimeException("AI job [{$aiJobId}] could not be found.");
        }

        $payload = is_array($job->input_payload) ? $job->input_payload : [];
        $cluster = $payload['cluster'] ?? null;

        if (! is_string($cluster) || $cluster === '') {
            throw new RuntimeException("Queued topic discovery job [{$aiJobId}] is missing a valid [cluster] value.");
        }

        return $this->run(new DiscoverContentTopicsData(
            cluster: $cluster,
            count: $this->positiveInt($payload['count'] ?? null, 'count'),
            audience: is_string($payload['audience'] ?? null) && $payload['audience'] !== '' ? $payload['audience'] : null,
            promptTemplateKey: is_string($payload['prompt_template_key'] ?? null) && $payload['prompt_template_key'] !== '' ? $payload['prompt_template_key'] : null,
            metadata: is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [],
        ), $aiJobId);
    }

    public function run(DiscoverContentTopicsData $data, ?int $aiJobId = null): AgentResult
    {
        $this->guardCluster($data->cluster);

        return $this->agent->run(new \App\AI\DTO\TopicDiscoveryInput(
            cluster: $data->cluster,
            targetCount: max(1, $data->count),
            audience: $data->audience,
            existingTopics: $this->existingTopics($data->cluster),
            knowledgeContext: $this->knowledgeContext->forPrompt(new KnowledgeContextQueryData(
                subject: str_replace('_', ' ', $data->cluster),
                keywords: array_values(array_filter([$data->audience, $data->cluster], static fn (mixed $value): bool => is_string($value) && $value !== '')),
                metadataFilters: $this->knowledgeMetadataFilters($data->metadata),
                maxEntries: 6,
                maxEntryCharacters: 280,
                maxTotalCharacters: 1600,
            )),
            metadata: array_filter([
                ...$data->metadata,
                'prompt_template_key' => $data->promptTemplateKey,
                'ai_job_id' => $aiJobId,
            ], static fn (mixed $value): bool => $value !== null),
        ));
    }

    private function guardCluster(string $cluster): void
    {
        if (in_array($cluster, ContentTopic::CLUSTERS, true)) {
            return;
        }

        throw new RuntimeException("Unsupported content cluster [{$cluster}] for topic discovery.");
    }

    /**
     * @return list<string>
     */
    private function existingTopics(string $cluster): array
    {
        return $this->topics->search(new ContentTopicFiltersData(
            cluster: $cluster,
            sort: '-created_at',
        ))
            ->pluck('title')
            ->filter(fn (mixed $title): bool => is_string($title) && $title !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, scalar|list<scalar>|null>
     */
    private function knowledgeMetadataFilters(array $metadata): array
    {
        $filters = $metadata['knowledge_context_filters'] ?? [];

        return is_array($filters) ? $filters : [];
    }

    private function positiveInt(mixed $value, string $field): int
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
            return (int) $value;
        }

        throw new RuntimeException("Queued topic discovery job is missing a valid [{$field}] value.");
    }
}
