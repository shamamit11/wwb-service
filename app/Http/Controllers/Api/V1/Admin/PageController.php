<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\ListPagesRequest;
use App\Http\Requests\Api\V1\Admin\StorePageRequest;
use App\Http\Requests\Api\V1\Admin\UpdatePageRequest;
use App\Http\Resources\Api\V1\PageResource;
use App\Models\Page;
use App\Modules\Pages\Services\CreatePageService;
use App\Modules\Pages\Services\DeletePageService;
use App\Modules\Pages\Services\ListAdminPagesService;
use App\Modules\Pages\Services\UpdatePageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    public function index(
        ListPagesRequest $request,
        ListAdminPagesService $service,
    ): AnonymousResourceCollection {
        return PageResource::collection($service->handle($request->toData()));
    }

    public function store(
        StorePageRequest $request,
        CreatePageService $service,
    ): JsonResponse {
        return (new PageResource($service->handle($request->toData((int) $request->user()->id))))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Page $page): PageResource
    {
        return new PageResource($page->loadMissing(['createdBy', 'updatedBy', 'seo']));
    }

    public function update(
        UpdatePageRequest $request,
        Page $page,
        UpdatePageService $service,
    ): PageResource {
        return new PageResource($service->handle($page, $request->toData((int) $request->user()->id)));
    }

    public function destroy(
        Page $page,
        DeletePageService $service,
    ): Response {
        $service->handle($page);

        return response()->noContent();
    }
}
