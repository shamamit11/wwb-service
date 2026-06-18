<?php

namespace App\Mcp\Resources;

use App\Mcp\Support\SerializesMcpPayloads;
use App\Models\ContentTopic;
use App\Modules\ContentTopics\Data\ContentTopicFiltersData;
use App\Modules\ContentTopics\Services\ListAdminContentTopicsService;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('Approved Topics')]
#[Uri('topics://approved')]
#[Description('Recent approved topics that are eligible for brief generation.')]
class ApprovedTopicsResource extends Resource
{
    use SerializesMcpPayloads;

    protected string $mimeType = 'application/json';

    public function __construct(
        private readonly ListAdminContentTopicsService $topics,
    ) {}

    public function handle(Request $request): Response
    {
        $topics = $this->topics->handle(new ContentTopicFiltersData(
            status: ContentTopic::STATUS_APPROVED,
            sort: '-approved_at',
        ))->take(10)->values();

        return Response::json([
            'topics' => $topics->map(fn (ContentTopic $topic): array => $this->serializeTopic($topic))->all(),
        ]);
    }
}
