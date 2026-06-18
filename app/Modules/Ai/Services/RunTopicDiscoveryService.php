<?php

namespace App\Modules\Ai\Services;

use App\AI\Agents\TopicDiscoveryAgent;
use App\AI\DTO\AgentResult;
use App\AI\DTO\TopicDiscoveryInput;
use App\Models\ContentTopic;
use App\Modules\Ai\Data\DiscoverContentTopicsData;
use App\Modules\ContentTopics\Data\ContentTopicFiltersData;
use App\Modules\ContentTopics\Repositories\ContentTopicRepository;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use App\Modules\KnowledgeBase\Services\KnowledgeContextService;
use RuntimeException;

class RunTopicDiscoveryService
{
    public function __construct(
        private readonly TopicDiscoveryAgent $agent,
        private readonly ContentTopicRepository $topics,
        private readonly KnowledgeContextService $knowledgeContext,
    ) {}

    public function handle(DiscoverContentTopicsData $data): AgentResult
    {
        if (! in_array($data->cluster, ContentTopic::CLUSTERS, true)) {
            throw new RuntimeException("Unsupported content cluster [{$data->cluster}] for topic discovery.");
        }

        return $this->agent->run(new TopicDiscoveryInput(
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
            ], static fn (mixed $value): bool => $value !== null),
        ));
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
}
