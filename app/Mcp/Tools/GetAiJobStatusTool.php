<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\SerializesMcpPayloads;
use App\Modules\Ai\Services\ReadAiJobService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('getAiJobStatus')]
#[Description('Read the current status, step counts, errors, and usage summary for an AI job.')]
#[IsReadOnly]
#[IsIdempotent]
class GetAiJobStatusTool extends Tool
{
    use SerializesMcpPayloads;

    public function __construct(
        private readonly ReadAiJobService $jobs,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'ai_job_id' => ['required', 'integer', 'min:1'],
        ]);

        return Response::structured([
            'job' => $this->serializeAiJob($this->jobs->handle((int) $validated['ai_job_id'])),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ai_job_id' => $schema->integer()->required()->description('AI job identifier.'),
        ];
    }
}
