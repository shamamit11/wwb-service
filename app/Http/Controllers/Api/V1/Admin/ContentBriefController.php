<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\GenerateBlogDraftRequest;
use App\Http\Requests\Api\V1\Admin\ListContentBriefsRequest;
use App\Http\Requests\Api\V1\Admin\UpdateContentBriefRequest;
use App\Http\Resources\Api\V1\AiJobResource;
use App\Http\Resources\Api\V1\ContentBriefResource;
use App\Models\ContentBrief;
use App\Modules\Ai\Services\QueueBlogDraftGenerationService;
use App\Modules\ContentBriefs\Services\ApproveContentBriefService;
use App\Modules\ContentBriefs\Services\ListAdminContentBriefsService;
use App\Modules\ContentBriefs\Services\ReadContentBriefService;
use App\Modules\ContentBriefs\Services\UpdateContentBriefService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ContentBriefController extends Controller
{
    public function index(
        ListContentBriefsRequest $request,
        ListAdminContentBriefsService $service,
    ): AnonymousResourceCollection {
        return ContentBriefResource::collection($service->handle($request->toData()));
    }

    public function show(
        ContentBrief $contentBrief,
        ReadContentBriefService $service,
    ): ContentBriefResource {
        return new ContentBriefResource($service->handle((int) $contentBrief->id));
    }

    public function update(
        UpdateContentBriefRequest $request,
        ContentBrief $contentBrief,
        UpdateContentBriefService $service,
    ): ContentBriefResource {
        return new ContentBriefResource($service->handle($contentBrief, $request->toData()));
    }

    public function approve(
        ContentBrief $contentBrief,
        ApproveContentBriefService $service,
    ): ContentBriefResource {
        return new ContentBriefResource($service->handle($contentBrief));
    }

    public function generateDraft(
        GenerateBlogDraftRequest $request,
        ContentBrief $contentBrief,
        QueueBlogDraftGenerationService $service,
    ): JsonResponse {
        return (new AiJobResource($service->handle($contentBrief, $request->toData())))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }
}
