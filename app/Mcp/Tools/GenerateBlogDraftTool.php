<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\SerializesMcpPayloads;
use App\Models\Post;
use App\Modules\Ai\Data\QueueBlogDraftGenerationData;
use App\Modules\Ai\Services\QueueBlogDraftGenerationService;
use App\Modules\ContentBriefs\Services\ReadContentBriefService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('generateBlogDraft')]
#[Description('Queue blog draft generation from an approved content brief. This creates drafts only and never publishes content.')]
class GenerateBlogDraftTool extends Tool
{
    use SerializesMcpPayloads;

    public function __construct(
        private readonly ReadContentBriefService $readBrief,
        private readonly QueueBlogDraftGenerationService $workflow,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'content_brief_id' => ['required', 'integer', 'min:1'],
            'author_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'template_id' => ['sometimes', 'nullable', 'integer', 'exists:templates,id'],
            'featured_media_id' => ['sometimes', 'nullable', 'integer', 'exists:media,id'],
            'visibility' => ['sometimes', 'string', 'in:'.implode(',', Post::VISIBILITIES)],
            'prompt_template_key' => ['sometimes', 'nullable', 'string', 'max:190'],
        ]);

        $brief = $this->readBrief->handle((int) $validated['content_brief_id']);
        $job = $this->workflow->handle($brief, new QueueBlogDraftGenerationData(
            authorUserId: isset($validated['author_user_id']) ? (int) $validated['author_user_id'] : null,
            categoryId: (int) $validated['category_id'],
            templateId: isset($validated['template_id']) ? (int) $validated['template_id'] : null,
            featuredMediaId: isset($validated['featured_media_id']) ? (int) $validated['featured_media_id'] : null,
            visibility: $validated['visibility'] ?? Post::VISIBILITY_PUBLIC,
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
            'content_brief_id' => $schema->integer()->required()->description('Approved content brief ID.'),
            'author_user_id' => $schema->integer()->description('Optional post author ID.'),
            'category_id' => $schema->integer()->required()->description('Category ID for the generated draft.'),
            'template_id' => $schema->integer()->description('Optional template ID.'),
            'featured_media_id' => $schema->integer()->description('Optional featured media ID.'),
            'visibility' => $schema->string()->description('Draft visibility.'),
            'prompt_template_key' => $schema->string()->description('Optional prompt template override.'),
        ];
    }
}
