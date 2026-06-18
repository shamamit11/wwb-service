<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\SerializesMcpPayloads;
use App\Models\ContentTopic;
use App\Modules\ContentTopics\Data\ContentTopicFiltersData;
use App\Modules\ContentTopics\Services\ListAdminContentTopicsService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('listContentTopics')]
#[Description('List content topics using the same filtering rules as the admin API.')]
#[IsReadOnly]
#[IsIdempotent]
class ListContentTopicsTool extends Tool
{
    use SerializesMcpPayloads;

    public function __construct(
        private readonly ListAdminContentTopicsService $topics,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:'.implode(',', ContentTopic::STATUSES)],
            'cluster' => ['nullable', 'string', 'in:'.implode(',', ContentTopic::CLUSTERS)],
            'source' => ['nullable', 'string', 'in:'.implode(',', ContentTopic::SOURCES)],
            'sort' => ['nullable', 'string', 'in:created_at,-created_at,updated_at,-updated_at,approved_at,-approved_at,used_at,-used_at,priority_score,-priority_score,title,-title'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $limit = (int) ($validated['limit'] ?? 25);
        $results = $this->topics->handle(new ContentTopicFiltersData(
            search: $validated['search'] ?? null,
            status: $validated['status'] ?? null,
            cluster: $validated['cluster'] ?? null,
            source: $validated['source'] ?? null,
            sort: $validated['sort'] ?? '-created_at',
        ));

        return Response::structured([
            'count' => min($results->count(), $limit),
            'topics' => $results->take($limit)->map(fn (ContentTopic $topic): array => $this->serializeTopic($topic))->values()->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Free-text search across topic fields.'),
            'status' => $schema->string()->description('Filter by topic status.'),
            'cluster' => $schema->string()->description('Filter by topic cluster.'),
            'source' => $schema->string()->description('Filter by topic source.'),
            'sort' => $schema->string()->description('Sort field, using the admin API sort options.'),
            'limit' => $schema->integer()->description('Maximum number of topics to return, capped at 100.'),
        ];
    }
}
