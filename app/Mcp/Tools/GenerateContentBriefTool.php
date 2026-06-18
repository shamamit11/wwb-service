<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\SerializesMcpPayloads;
use App\Modules\Ai\Services\ContentBriefWorkflow;
use App\Modules\ContentTopics\Services\ReadContentTopicService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('generateContentBrief')]
#[Description('Generate or reuse a content brief for an approved topic. This follows the same service-layer rules as the admin API.')]
class GenerateContentBriefTool extends Tool
{
    use SerializesMcpPayloads;

    public function __construct(
        private readonly ReadContentTopicService $readTopic,
        private readonly ContentBriefWorkflow $workflow,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'content_topic_id' => ['required', 'integer', 'min:1'],
            'prompt_template_key' => ['sometimes', 'nullable', 'string', 'max:190'],
        ]);

        $topic = $this->readTopic->handle((int) $validated['content_topic_id']);
        $result = $this->workflow->generate(
            $topic,
            $validated['prompt_template_key'] ?? null,
        );

        return Response::structured([
            'created' => $result->wasCreated,
            'brief' => $this->serializeBrief($result->brief->loadMissing('topic')),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'content_topic_id' => $schema->integer()->required()->description('Approved topic ID to generate a brief for.'),
            'prompt_template_key' => $schema->string()->description('Optional prompt template override.'),
        ];
    }
}
