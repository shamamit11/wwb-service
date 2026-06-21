<?php

namespace App\Mcp\Tools;

use App\AI\DTO\TopicSuggestionData;
use App\AI\Tools\CheckDuplicateTopicTool;
use App\AI\Tools\SaveTopicIdeaTool;
use App\Mcp\Support\SerializesMcpPayloads;
use App\Models\ContentTopic;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('createTopicSuggestion')]
#[Description('Create a suggested content topic after running duplicate checks. This never publishes or approves topics.')]
class CreateTopicSuggestionTool extends Tool
{
    use SerializesMcpPayloads;

    public function __construct(
        private readonly CheckDuplicateTopicTool $duplicates,
        private readonly SaveTopicIdeaTool $saveTopic,
        private readonly \App\Modules\Ai\Services\ResolveTopicDiscoveryClusterService $clusters,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:160'],
            'cluster' => ['sometimes', 'nullable', 'string', 'in:'.implode(',', ContentTopic::CLUSTERS)],
            'primary_keyword' => ['sometimes', 'nullable', 'string', 'max:255'],
            'secondary_keywords' => ['sometimes', 'array'],
            'secondary_keywords.*' => ['string', 'max:255'],
            'search_intent' => ['sometimes', 'nullable', 'string', 'max:255'],
            'priority_score' => ['sometimes', 'nullable', 'numeric', 'between:0,999.99'],
            'difficulty_note' => ['sometimes', 'nullable', 'string'],
            'summary' => ['sometimes', 'nullable', 'string'],
            'audience' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $category = \App\Models\Category::query()->where('is_active', true)->find((int) $validated['category_id']);

        if (! $category instanceof \App\Models\Category) {
            return Response::structured([
                'created' => false,
                'duplicate_check' => ['is_duplicate' => false, 'matches' => []],
                'topic' => null,
                'error' => 'Active category could not be found.',
            ]);
        }

        $cluster = $validated['cluster'] ?? $this->clusters->forCategory($category);

        if (! is_string($cluster) || $cluster === '') {
            return Response::structured([
                'created' => false,
                'duplicate_check' => ['is_duplicate' => false, 'matches' => []],
                'topic' => null,
                'error' => 'Category is not mapped to a supported topic cluster.',
            ]);
        }

        $duplicateCheck = $this->duplicates->check(
            title: $validated['title'],
            categoryId: (int) $validated['category_id'],
            primaryKeyword: $validated['primary_keyword'] ?? null,
            slug: $validated['slug'],
        );

        if ($duplicateCheck['is_duplicate']) {
            return Response::structured([
                'created' => false,
                'duplicate_check' => $duplicateCheck,
                'topic' => null,
            ]);
        }

        $topic = $this->saveTopic->save(new TopicSuggestionData(
            title: $validated['title'],
            slug: $validated['slug'],
            cluster: $cluster,
            primaryKeyword: $validated['primary_keyword'] ?? null,
            secondaryKeywords: $this->normalizeStringList($validated['secondary_keywords'] ?? []),
            searchIntent: $validated['search_intent'] ?? null,
            priorityScore: isset($validated['priority_score']) ? (string) $validated['priority_score'] : null,
            difficultyNote: $validated['difficulty_note'] ?? null,
            summary: $validated['summary'] ?? null,
        ), (int) $validated['category_id'], $validated['audience'] ?? null);

        return Response::structured([
            'created' => true,
            'duplicate_check' => $duplicateCheck,
            'topic' => $this->serializeTopic($topic),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category_id' => $schema->integer()->required()->description('Category ID for the suggested topic.'),
            'title' => $schema->string()->required()->description('Proposed topic title.'),
            'slug' => $schema->string()->required()->description('Preferred slug for the suggested topic.'),
            'cluster' => $schema->string()->description('Optional topic cluster override. Usually derived from the category.'),
            'primary_keyword' => $schema->string()->description('Primary SEO keyword if known.'),
            'secondary_keywords' => $schema->array(
                items: $schema->string()
            )->description('Supporting keywords.'),
            'search_intent' => $schema->string()->description('Expected search intent.'),
            'priority_score' => $schema->number()->description('Optional topic priority score.'),
            'difficulty_note' => $schema->string()->description('Optional editorial difficulty note.'),
            'summary' => $schema->string()->description('Short summary used to seed topic notes.'),
            'audience' => $schema->string()->description('Optional audience context included in topic notes.'),
        ];
    }
}
