<?php

namespace App\Modules\Ai\Services;

use App\AI\Agents\TopicDiscoveryAgent;
use App\AI\DTO\AgentResult;
use App\AI\DTO\TopicDiscoveryInput;
use App\Jobs\AI\DiscoverContentTopicsJob;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\Category;
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
        private readonly ResolveTopicDiscoveryClusterService $clusterResolver,
    ) {}

    public function dispatch(DiscoverContentTopicsData $data, ?int $retryOfAiJobId = null, int $attempts = 1): AiJob
    {
        $category = $this->resolveCategory($data->categoryId);
        $cluster = $this->resolveCluster($category);

        $job = $this->jobs->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_TOPIC_DISCOVERY,
            status: AiJob::STATUS_QUEUED,
            entityType: 'content_topic_batch',
            inputPayload: [
                'category_id' => (int) $category->id,
                'cluster' => $cluster,
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
        $categoryId = $this->positiveInt($payload['category_id'] ?? null, 'category_id');

        return $this->run(new DiscoverContentTopicsData(
            categoryId: $categoryId,
            count: $this->positiveInt($payload['count'] ?? null, 'count'),
            audience: is_string($payload['audience'] ?? null) && $payload['audience'] !== '' ? $payload['audience'] : null,
            promptTemplateKey: is_string($payload['prompt_template_key'] ?? null) && $payload['prompt_template_key'] !== '' ? $payload['prompt_template_key'] : null,
            metadata: is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [],
        ), $aiJobId);
    }

    public function run(DiscoverContentTopicsData $data, ?int $aiJobId = null): AgentResult
    {
        $category = $this->resolveCategory($data->categoryId);
        $cluster = $this->resolveCluster($category);

        return $this->agent->run(new TopicDiscoveryInput(
            categoryId: (int) $category->id,
            categoryName: $category->name,
            categorySlug: $category->slug,
            cluster: $cluster,
            targetCount: max(1, $data->count),
            audience: $data->audience,
            existingTopics: $this->existingTopics((int) $category->id),
            knowledgeContext: $this->knowledgeContext->forPrompt(new KnowledgeContextQueryData(
                subject: $category->name,
                keywords: array_values(array_filter([$data->audience, $category->name, $category->slug, $cluster], static fn (mixed $value): bool => is_string($value) && $value !== '')),
                metadataFilters: $this->knowledgeMetadataFilters($category, $cluster, $data->metadata),
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

    private function resolveCategory(int $categoryId): Category
    {
        $category = Category::query()
            ->where('is_active', true)
            ->find($categoryId);

        if ($category instanceof Category) {
            return $category;
        }

        throw new RuntimeException("Active category [{$categoryId}] could not be found for topic discovery.");
    }

    /**
     * @return list<string>
     */
    private function existingTopics(int $categoryId): array
    {
        return $this->topics->search(new ContentTopicFiltersData(
            categoryId: $categoryId,
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
    private function knowledgeMetadataFilters(Category $category, string $cluster, array $metadata): array
    {
        $filters = is_array($metadata['knowledge_context_filters'] ?? null)
            ? $metadata['knowledge_context_filters']
            : [];

        $filters['category_slugs'] = array_values(array_unique(array_filter([
            ...($filters['category_slugs'] ?? []),
            $category->slug,
        ], static fn (mixed $value): bool => is_string($value) && $value !== '')));

        $filters['clusters'] = array_values(array_unique(array_filter([
            ...($filters['clusters'] ?? []),
            $cluster,
        ], static fn (mixed $value): bool => is_string($value) && $value !== '')));

        return $filters;
    }

    private function resolveCluster(Category $category): string
    {
        $cluster = $this->clusterResolver->forCategory($category);

        if (is_string($cluster) && $cluster !== '') {
            return $cluster;
        }

        throw new RuntimeException("Category [{$category->slug}] is not mapped to a supported topic discovery cluster.");
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
