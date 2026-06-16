<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\LinkKnowledgeBaseEntryToPostRequest;
use App\Http\Requests\Api\V1\Admin\LinkKnowledgeBaseEntryToTopicRequest;
use App\Http\Requests\Api\V1\Admin\ListKnowledgeBaseEntriesRequest;
use App\Http\Requests\Api\V1\Admin\StoreKnowledgeBaseEntryRequest;
use App\Http\Requests\Api\V1\Admin\UpdateKnowledgeBaseEntryRequest;
use App\Http\Resources\Api\V1\KnowledgeBaseEntryResource;
use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Services\CreateKnowledgeBaseEntryService;
use App\Modules\KnowledgeBase\Services\DeleteKnowledgeBaseEntryService;
use App\Modules\KnowledgeBase\Services\LinkKnowledgeBaseEntryToPostService;
use App\Modules\KnowledgeBase\Services\LinkKnowledgeBaseEntryToTopicService;
use App\Modules\KnowledgeBase\Services\ListAdminKnowledgeBaseEntriesService;
use App\Modules\KnowledgeBase\Services\UpdateKnowledgeBaseEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class KnowledgeBaseEntryController extends Controller
{
    public function index(
        ListKnowledgeBaseEntriesRequest $request,
        ListAdminKnowledgeBaseEntriesService $service,
    ): AnonymousResourceCollection {
        return KnowledgeBaseEntryResource::collection($service->handle($request->toData()));
    }

    public function store(
        StoreKnowledgeBaseEntryRequest $request,
        CreateKnowledgeBaseEntryService $service,
    ): JsonResponse {
        return (new KnowledgeBaseEntryResource($service->handle($request->toData((int) $request->user()->id))))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(KnowledgeBaseEntry $knowledgeBase): KnowledgeBaseEntryResource
    {
        return new KnowledgeBaseEntryResource($knowledgeBase->loadMissing(['createdBy', 'updatedBy', 'featuredMedia']));
    }

    public function update(
        UpdateKnowledgeBaseEntryRequest $request,
        KnowledgeBaseEntry $knowledgeBase,
        UpdateKnowledgeBaseEntryService $service,
    ): KnowledgeBaseEntryResource {
        return new KnowledgeBaseEntryResource(
            $service->handle($knowledgeBase, $request->toData((int) $request->user()->id)),
        );
    }

    public function destroy(
        KnowledgeBaseEntry $knowledgeBase,
        DeleteKnowledgeBaseEntryService $service,
    ): Response {
        $service->handle($knowledgeBase);

        return response()->noContent();
    }

    public function linkPost(
        LinkKnowledgeBaseEntryToPostRequest $request,
        KnowledgeBaseEntry $knowledgeBase,
        LinkKnowledgeBaseEntryToPostService $service,
    ): KnowledgeBaseEntryResource {
        return new KnowledgeBaseEntryResource($service->handle($knowledgeBase, $request->toData()));
    }

    public function linkTopic(
        LinkKnowledgeBaseEntryToTopicRequest $request,
        KnowledgeBaseEntry $knowledgeBase,
        LinkKnowledgeBaseEntryToTopicService $service,
    ): KnowledgeBaseEntryResource {
        return new KnowledgeBaseEntryResource($service->handle($knowledgeBase, $request->toData()));
    }
}
