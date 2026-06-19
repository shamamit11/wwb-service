<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\SerializesMcpPayloads;
use App\Modules\Ai\Data\QueuePostRewriteData;
use App\Modules\Ai\Services\QueuePostRewriteService;
use App\Modules\Posts\Services\ReadAdminPostService;
use App\Modules\Posts\Services\RewritePostDraftService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('rewritePostDraft')]
#[Description('Queue a review-only draft rewrite for an existing AI-generated draft post. Supports full-draft and targeted block regeneration and never publishes content.')]
class RewritePostDraftTool extends Tool
{
    use SerializesMcpPayloads;

    public function __construct(
        private readonly ReadAdminPostService $posts,
        private readonly QueuePostRewriteService $workflow,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'post_id' => ['required', 'string'],
            'scope' => ['required', 'string', 'in:'.implode(',', [
                RewritePostDraftService::SCOPE_FULL_DRAFT,
                RewritePostDraftService::SCOPE_SECTION,
                RewritePostDraftService::SCOPE_PARAGRAPH,
            ])],
            'target_block_ids' => ['sometimes', 'array'],
            'target_block_ids.*' => ['integer', 'exists:post_blocks,id'],
            'instructions' => ['sometimes', 'nullable', 'string'],
            'prompt_template_key' => ['sometimes', 'nullable', 'string', 'max:190'],
        ]);

        $post = $this->posts->handle((string) $validated['post_id']);
        $job = $this->workflow->handle($post, new QueuePostRewriteData(
            scope: (string) $validated['scope'],
            targetBlockIds: array_values(array_map('intval', $validated['target_block_ids'] ?? [])),
            instructions: isset($validated['instructions']) ? (string) $validated['instructions'] : null,
            promptTemplateKey: $validated['prompt_template_key'] ?? null,
        ));

        return Response::structured([
            'queued' => true,
            'job' => $this->serializeAiJob($job),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'post_id' => $schema->string()->required()->description('Draft post numeric ID or ULID.'),
            'scope' => $schema->string()->required()->description('Rewrite scope: full_draft, section, or paragraph.'),
            'target_block_ids' => $schema->array()->items($schema->integer())->description('Required for section or paragraph rewrites.'),
            'instructions' => $schema->string()->description('Optional editorial rewrite instructions.'),
            'prompt_template_key' => $schema->string()->description('Optional prompt template override.'),
        ];
    }
}
