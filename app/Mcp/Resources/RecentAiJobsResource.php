<?php

namespace App\Mcp\Resources;

use App\Mcp\Support\SerializesMcpPayloads;
use App\Models\AiJob;
use App\Modules\Ai\Data\AiJobFiltersData;
use App\Modules\Ai\Services\ListAdminAiJobsService;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('Recent AI Jobs')]
#[Uri('ai-jobs://recent')]
#[Description('Recent AI jobs across topic discovery, brief generation, and draft generation workflows.')]
class RecentAiJobsResource extends Resource
{
    use SerializesMcpPayloads;

    protected string $mimeType = 'application/json';

    public function __construct(
        private readonly ListAdminAiJobsService $jobs,
    ) {}

    public function handle(Request $request): Response
    {
        $jobs = $this->jobs->handle(new AiJobFiltersData(
            sort: '-created_at',
        ))->take(10)->values();

        return Response::json([
            'jobs' => $jobs->map(fn (AiJob $job): array => $this->serializeAiJob($job))->all(),
        ]);
    }
}
