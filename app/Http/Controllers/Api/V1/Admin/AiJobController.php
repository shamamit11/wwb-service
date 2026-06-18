<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\ListAiJobsRequest;
use App\Http\Requests\Api\V1\Admin\RetryAiJobRequest;
use App\Http\Resources\Api\V1\AiJobResource;
use App\Models\AiJob;
use App\Modules\Ai\Services\ListAdminAiJobsService;
use App\Modules\Ai\Services\ReadAiJobService;
use App\Modules\Ai\Services\RetryAiJobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class AiJobController extends Controller
{
    public function index(
        ListAiJobsRequest $request,
        ListAdminAiJobsService $service,
    ): AnonymousResourceCollection {
        return AiJobResource::collection($service->handle($request->toData()));
    }

    public function show(
        AiJob $aiJob,
        ReadAiJobService $service,
    ): AiJobResource {
        return new AiJobResource($service->handle((int) $aiJob->id));
    }

    public function retry(
        RetryAiJobRequest $request,
        AiJob $aiJob,
        RetryAiJobService $service,
    ): JsonResponse {
        return (new AiJobResource($service->handle($aiJob)))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }
}
