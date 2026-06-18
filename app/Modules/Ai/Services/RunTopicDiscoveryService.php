<?php

namespace App\Modules\Ai\Services;

use App\AI\Agents\TopicDiscoveryAgent;
use App\AI\DTO\AgentResult;
use App\AI\DTO\TopicDiscoveryInput;
use App\Models\ContentTopic;
use App\Models\KnowledgeBaseEntry;
use App\Modules\Ai\Data\DiscoverContentTopicsData;
use App\Modules\ContentTopics\Data\ContentTopicFiltersData;
use App\Modules\ContentTopics\Repositories\ContentTopicRepository;
use App\Modules\KnowledgeBase\Data\KnowledgeBaseEntryFiltersData;
use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;
use RuntimeException;

class RunTopicDiscoveryService
{
    public function __construct(
        private readonly TopicDiscoveryAgent $agent,
        private readonly ContentTopicRepository $topics,
        private readonly KnowledgeBaseEntryRepository $knowledgeBase,
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
            knowledgeContext: $this->knowledgeContext(),
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
     * @return list<string>
     */
    private function knowledgeContext(): array
    {
        return $this->knowledgeBase->searchAdmin(new KnowledgeBaseEntryFiltersData(
            status: KnowledgeBaseEntry::STATUS_ACTIVE,
            sort: '-updated_at',
        ))
            ->take(10)
            ->map(function (KnowledgeBaseEntry $entry): ?string {
                $summary = trim((string) ($entry->summary ?? ''));
                $content = trim((string) ($entry->content_markdown ?? ''));
                $excerpt = $summary !== '' ? $summary : mb_substr($content, 0, 240);
                $excerpt = trim($excerpt);

                if ($excerpt === '') {
                    return null;
                }

                return "{$entry->title}: {$excerpt}";
            })
            ->filter(fn (?string $line): bool => is_string($line) && $line !== '')
            ->values()
            ->all();
    }
}
