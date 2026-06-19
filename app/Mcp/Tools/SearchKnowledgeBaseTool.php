<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\SerializesMcpPayloads;
use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use App\Modules\KnowledgeBase\Services\KnowledgeContextService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('searchKnowledgeBase')]
#[Description('Search active knowledge-base entries and return grounded context formatted for agents.')]
#[IsReadOnly]
#[IsIdempotent]
class SearchKnowledgeBaseTool extends Tool
{
    use SerializesMcpPayloads;

    public function __construct(
        private readonly KnowledgeContextService $knowledgeContext,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'keywords' => ['sometimes', 'array'],
            'keywords.*' => ['string', 'max:255'],
            'entry_types' => ['sometimes', 'array'],
            'entry_types.*' => ['string', 'max:80'],
            'metadata_filters' => ['sometimes', 'array'],
            'max_entries' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'max_entry_characters' => ['sometimes', 'integer', 'min:80', 'max:1200'],
            'max_total_characters' => ['sometimes', 'integer', 'min:120', 'max:4000'],
            'candidate_pool_size' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $query = new KnowledgeContextQueryData(
            subject: $validated['subject'] ?? null,
            keywords: $this->normalizeStringList($validated['keywords'] ?? []),
            entryTypes: $this->normalizeStringList($validated['entry_types'] ?? []),
            metadataFilters: is_array($validated['metadata_filters'] ?? null) ? $validated['metadata_filters'] : [],
            maxEntries: (int) ($validated['max_entries'] ?? 6),
            maxEntryCharacters: (int) ($validated['max_entry_characters'] ?? 320),
            maxTotalCharacters: (int) ($validated['max_total_characters'] ?? 1800),
            candidatePoolSize: (int) ($validated['candidate_pool_size'] ?? 25),
        );

        $entries = $this->knowledgeContext->search($query)
            ->take($query->maxEntries)
            ->values();

        return Response::structured([
            'subject' => $query->subject,
            'entries' => $entries->map(fn (KnowledgeBaseEntry $entry): array => $this->serializeKnowledgeBaseEntry($entry))->all(),
            'context' => $this->knowledgeContext->forPrompt($query),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'subject' => $schema->string()->description('Primary topic, question, or editorial subject to ground.'),
            'keywords' => $schema->array(
                items: $schema->string()
            )->description('Optional supporting keywords to improve retrieval.'),
            'entry_types' => $schema->array(
                items: $schema->string()
            )->description('Optional knowledge-base entry types to constrain retrieval.'),
            'metadata_filters' => $schema->object()->description('Optional metadata filters applied after entry retrieval.'),
            'max_entries' => $schema->integer()->description('Maximum number of entries returned, capped at 10.'),
            'max_entry_characters' => $schema->integer()->description('Maximum characters per formatted context line.'),
            'max_total_characters' => $schema->integer()->description('Maximum combined context size for prompt-safe output.'),
            'candidate_pool_size' => $schema->integer()->description('How many active entries to consider before scoring.'),
        ];
    }
}
