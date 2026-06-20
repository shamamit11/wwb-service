<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\ListContentTopicsRequest;
use App\Http\Requests\Api\V1\Admin\StoreContentTopicRequest;
use App\Http\Requests\Api\V1\Admin\TransitionContentTopicRequest;
use App\Http\Requests\Api\V1\Admin\UpdateContentTopicRequest;
use App\Http\Resources\Api\V1\ContentBriefResource;
use App\Http\Resources\Api\V1\ContentTopicResource;
use App\Models\ContentTopic;
use App\Modules\Ai\Services\ContentBriefWorkflow;
use App\Modules\ContentTopics\Services\ApproveContentTopicService;
use App\Modules\ContentTopics\Services\CreateContentTopicService;
use App\Modules\ContentTopics\Services\DeleteContentTopicService;
use App\Modules\ContentTopics\Services\ListAdminContentTopicsService;
use App\Modules\ContentTopics\Services\MarkContentTopicUsedService;
use App\Modules\ContentTopics\Services\ReadContentTopicService;
use App\Modules\ContentTopics\Services\RejectContentTopicService;
use App\Modules\ContentTopics\Services\UpdateContentTopicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ContentTopicController extends Controller
{
    public function index(
        ListContentTopicsRequest $request,
        ListAdminContentTopicsService $service,
    ): AnonymousResourceCollection {
        return ContentTopicResource::collection($service->handle($request->toData()));
    }

    public function store(
        StoreContentTopicRequest $request,
        CreateContentTopicService $service,
    ): JsonResponse {
        return (new ContentTopicResource($service->handle($request->toData())))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(
        ContentTopic $contentTopic,
        ReadContentTopicService $service,
    ): ContentTopicResource {
        return new ContentTopicResource($service->handle((int) $contentTopic->id));
    }

    public function update(
        UpdateContentTopicRequest $request,
        ContentTopic $contentTopic,
        UpdateContentTopicService $service,
    ): ContentTopicResource {
        return new ContentTopicResource($service->handle($contentTopic, $request->toData()));
    }

    public function destroy(
        ContentTopic $contentTopic,
        DeleteContentTopicService $service,
    ): Response {
        $service->handle($contentTopic);

        return response()->noContent();
    }

    public function approve(
        TransitionContentTopicRequest $request,
        ContentTopic $contentTopic,
        ApproveContentTopicService $service,
    ): ContentTopicResource {
        return new ContentTopicResource($service->handle($contentTopic, $request->notes()));
    }

    public function reject(
        TransitionContentTopicRequest $request,
        ContentTopic $contentTopic,
        RejectContentTopicService $service,
    ): ContentTopicResource {
        return new ContentTopicResource($service->handle($contentTopic, $request->notes()));
    }

    public function markUsed(
        TransitionContentTopicRequest $request,
        ContentTopic $contentTopic,
        MarkContentTopicUsedService $service,
    ): ContentTopicResource {
        return new ContentTopicResource($service->handle($contentTopic, $request->notes()));
    }

    public function generateBrief(
        ContentTopic $contentTopic,
        ContentBriefWorkflow $service,
    ): JsonResponse {
        $job = $service->queue($contentTopic);

        if ($job !== null) {
            return response()->json([
                'message' => 'Content brief generation queued.',
                'data' => (object) [],
                'meta' => [
                    'ai_job_id' => $job->id,
                    'ai_job_status' => $job->status,
                ],
            ], Response::HTTP_ACCEPTED);
        }

        $result = $service->generate($contentTopic);

        return (new ContentBriefResource($result->brief))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
