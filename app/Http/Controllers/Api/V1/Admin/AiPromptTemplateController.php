<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\ActivateAiPromptTemplateVersionRequest;
use App\Http\Requests\Api\V1\Admin\ListAiPromptTemplatesRequest;
use App\Http\Requests\Api\V1\Admin\StoreAiPromptTemplateRequest;
use App\Http\Requests\Api\V1\Admin\StoreAiPromptTemplateVersionRequest;
use App\Http\Requests\Api\V1\Admin\UpdateAiPromptTemplateRequest;
use App\Http\Resources\Api\V1\AiPromptTemplateResource;
use App\Http\Resources\Api\V1\AiPromptTemplateVersionResource;
use App\Models\AiPromptTemplate;
use App\Modules\Ai\Services\ActivateAiPromptTemplateVersionService;
use App\Modules\Ai\Services\CreateAiPromptTemplateService;
use App\Modules\Ai\Services\CreateAiPromptTemplateVersionService;
use App\Modules\Ai\Services\ListAdminAiPromptTemplatesService;
use App\Modules\Ai\Services\ReadAiPromptTemplateService;
use App\Modules\Ai\Services\UpdateAiPromptTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class AiPromptTemplateController extends Controller
{
    public function index(
        ListAiPromptTemplatesRequest $request,
        ListAdminAiPromptTemplatesService $service,
    ): AnonymousResourceCollection {
        return AiPromptTemplateResource::collection($service->handle($request->toData()));
    }

    public function store(
        StoreAiPromptTemplateRequest $request,
        CreateAiPromptTemplateService $service,
    ): JsonResponse {
        return (new AiPromptTemplateResource($service->handle($request->toData())))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(
        AiPromptTemplate $aiPrompt,
        ReadAiPromptTemplateService $service,
    ): AiPromptTemplateResource {
        return new AiPromptTemplateResource($service->handle((int) $aiPrompt->id));
    }

    public function update(
        UpdateAiPromptTemplateRequest $request,
        AiPromptTemplate $aiPrompt,
        UpdateAiPromptTemplateService $service,
    ): AiPromptTemplateResource {
        return new AiPromptTemplateResource($service->handle($aiPrompt, $request->toData()));
    }

    public function storeVersion(
        StoreAiPromptTemplateVersionRequest $request,
        AiPromptTemplate $aiPrompt,
        CreateAiPromptTemplateVersionService $service,
    ): JsonResponse {
        return (new AiPromptTemplateVersionResource($service->handle($aiPrompt, $request->toData())))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function activateVersion(
        ActivateAiPromptTemplateVersionRequest $request,
        AiPromptTemplate $aiPrompt,
        int $versionId,
        ActivateAiPromptTemplateVersionService $service,
    ): AiPromptTemplateResource {
        return new AiPromptTemplateResource($service->handle($aiPrompt, $versionId));
    }
}
