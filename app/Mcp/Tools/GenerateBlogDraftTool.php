<?php

namespace App\Mcp\Tools;

use App\AI\Enums\BlogDraftGenerationMode;
use App\Mcp\Support\SerializesMcpPayloads;
use App\Models\Post;
use App\Modules\Ai\Data\QueueBlogDraftGenerationData;
use App\Modules\Ai\Services\QueueBlogDraftGenerationService;
use App\Modules\ContentTopics\Services\ReadContentTopicService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('generateBlogDraft')]
#[Description('Queue blog draft generation from an approved topic. This creates drafts only and never publishes content.')]
class GenerateBlogDraftTool extends Tool
{
    use SerializesMcpPayloads;

    public function __construct(
        private readonly ReadContentTopicService $readTopic,
        private readonly QueueBlogDraftGenerationService $workflow,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'content_topic_id' => ['required', 'integer', 'min:1'],
            'author_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'featured_media_id' => ['sometimes', 'nullable', 'integer', 'exists:media,id'],
            'visibility' => ['sometimes', 'string', 'in:'.implode(',', Post::VISIBILITIES)],
            'generation_mode' => ['sometimes', 'nullable', 'string', 'in:'.implode(',', BlogDraftGenerationMode::values())],
        ]);

        $topic = $this->readTopic->handle((int) $validated['content_topic_id']);
        $job = $this->workflow->handle($topic, new QueueBlogDraftGenerationData(
            authorUserId: isset($validated['author_user_id']) ? (int) $validated['author_user_id'] : null,
            categoryId: (int) $validated['category_id'],
            featuredMediaId: isset($validated['featured_media_id']) ? (int) $validated['featured_media_id'] : null,
            visibility: $validated['visibility'] ?? Post::VISIBILITY_PUBLIC,
            generationMode: $validated['generation_mode'] ?? null,
        ));

        return Response::structured([
            'queued' => true,
            'job' => $this->serializeAiJob($job),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'content_topic_id' => $schema->integer()->required()->description('Approved topic ID.'),
            'author_user_id' => $schema->integer()->description('Optional post author ID.'),
            'category_id' => $schema->integer()->required()->description('Category ID for the generated draft.'),
            'featured_media_id' => $schema->integer()->description('Optional featured media ID.'),
            'visibility' => $schema->string()->description('Draft visibility.'),
            'generation_mode' => $schema->string()->description('Optional editorial mode: tutorial, comparison, opinionated_analysis, or checklist.'),
        ];
    }
}
