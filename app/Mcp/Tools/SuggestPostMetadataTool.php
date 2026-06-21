<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\SerializesMcpPayloads;
use App\Modules\Ai\Data\QueuePostMetadataSuggestionData;
use App\Modules\Ai\Services\QueuePostMetadataSuggestionService;
use App\Modules\Posts\Services\ReadAdminPostService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('suggestPostMetadata')]
#[Description('Queue review-only AI metadata suggestions for an existing draft or post. This suggests titles, excerpt, SEO metadata, and focus keyword without publishing or auto-applying changes.')]
class SuggestPostMetadataTool extends Tool
{
    use SerializesMcpPayloads;

    public function __construct(
        private readonly ReadAdminPostService $posts,
        private readonly QueuePostMetadataSuggestionService $workflow,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'post_id' => ['required', 'string'],
            'instructions' => ['sometimes', 'nullable', 'string'],
        ]);

        $post = $this->posts->handle((string) $validated['post_id']);
        $job = $this->workflow->handle($post, new QueuePostMetadataSuggestionData(
            instructions: isset($validated['instructions']) ? (string) $validated['instructions'] : null,
        ));

        return Response::structured([
            'queued' => true,
            'job' => $this->serializeAiJob($job),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'post_id' => $schema->string()->required()->description('Draft or post numeric ID or ULID.'),
            'instructions' => $schema->string()->description('Optional editorial or SEO guidance for the suggestion pass.'),
        ];
    }
}
