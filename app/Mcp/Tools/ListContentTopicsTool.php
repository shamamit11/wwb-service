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
            'recommendation' => ['nullable', 'string', 'in:'.implode(',', ContentTopic::RECOMMENDATIONS)],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'cluster' => ['nullable', 'string', 'in:'.implode(',', ContentTopic::CLUSTERS)],
            'source' => ['nullable', 'string', 'in:'.implode(',', ContentTopic::SOURCES)],
            'is_duplicate' => ['nullable', 'boolean'],
            'has_draft_generation_job' => ['nullable', 'boolean'],
            'priority_score_min' => ['nullable', 'numeric', 'between:0,999.99'],
            'priority_score_max' => ['nullable', 'numeric', 'between:0,999.99'],
            'sort' => ['nullable', 'string', 'in:created_at,-created_at,updated_at,-updated_at,approved_at,-approved_at,used_at,-used_at,priority_score,-priority_score,title,-title'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $limit = (int) ($validated['limit'] ?? 25);
        $results = $this->topics->handle(new ContentTopicFiltersData(
            search: $validated['search'] ?? null,
            status: $validated['status'] ?? null,
            recommendation: $validated['recommendation'] ?? null,
            categoryId: isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            cluster: $validated['cluster'] ?? null,
            source: $validated['source'] ?? null,
            isDuplicate: array_key_exists('is_duplicate', $validated) ? (bool) $validated['is_duplicate'] : null,
            hasDraftGenerationJob: array_key_exists('has_draft_generation_job', $validated) ? (bool) $validated['has_draft_generation_job'] : null,
            priorityScoreMin: isset($validated['priority_score_min']) ? (float) $validated['priority_score_min'] : null,
            priorityScoreMax: isset($validated['priority_score_max']) ? (float) $validated['priority_score_max'] : null,
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
            'recommendation' => $schema->string()->description('Filter by the derived editorial recommendation band.'),
            'category_id' => $schema->integer()->description('Filter by category ID.'),
            'cluster' => $schema->string()->description('Filter by topic cluster.'),
            'source' => $schema->string()->description('Filter by topic source.'),
            'is_duplicate' => $schema->boolean()->description('Filter duplicate discoveries on or off.'),
            'has_draft_generation_job' => $schema->boolean()->description('Filter topics with queued or completed draft-generation jobs.'),
            'priority_score_min' => $schema->number()->description('Minimum topic priority score.'),
            'priority_score_max' => $schema->number()->description('Maximum topic priority score.'),
            'sort' => $schema->string()->description('Sort field, using the admin API sort options.'),
            'limit' => $schema->integer()->description('Maximum number of topics to return, capped at 100.'),
        ];
    }
}
