<?php

namespace App\Mcp\Resources;

use App\Mcp\Support\SerializesMcpPayloads;
use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use App\Modules\KnowledgeBase\Services\KnowledgeContextService;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('Knowledge Base Entries')]
#[Uri('knowledge-base://entries')]
#[Description('Recent active knowledge-base entries that can be used as editorial grounding context.')]
class KnowledgeBaseEntriesResource extends Resource
{
    use SerializesMcpPayloads;

    protected string $mimeType = 'application/json';

    public function __construct(
        private readonly KnowledgeContextService $knowledgeContext,
    ) {}

    public function handle(Request $request): Response
    {
        $entries = $this->knowledgeContext->search(new KnowledgeContextQueryData(
            candidatePoolSize: 10,
        ))->take(10)->values();

        return Response::json([
            'entries' => $entries->map(fn (KnowledgeBaseEntry $entry): array => $this->serializeKnowledgeBaseEntry($entry))->all(),
        ]);
    }
}
