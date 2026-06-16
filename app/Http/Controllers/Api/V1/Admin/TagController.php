<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreTagRequest;
use App\Http\Requests\Api\V1\Admin\UpdateTagRequest;
use App\Http\Resources\Api\V1\TagResource;
use App\Models\Tag;
use App\Modules\Tags\Services\CreateTagService;
use App\Modules\Tags\Services\DeleteTagService;
use App\Modules\Tags\Services\ListAdminTagsService;
use App\Modules\Tags\Services\UpdateTagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    public function index(ListAdminTagsService $service): AnonymousResourceCollection
    {
        return TagResource::collection($service->handle());
    }

    public function store(
        StoreTagRequest $request,
        CreateTagService $service,
    ): JsonResponse {
        return (new TagResource($service->handle($request->toData())))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Tag $tag): TagResource
    {
        return new TagResource($tag);
    }

    public function update(
        UpdateTagRequest $request,
        Tag $tag,
        UpdateTagService $service,
    ): TagResource {
        return new TagResource($service->handle($tag, $request->toData()));
    }

    public function destroy(
        Tag $tag,
        DeleteTagService $service,
    ): Response {
        $service->handle($tag);

        return response()->noContent();
    }
}
