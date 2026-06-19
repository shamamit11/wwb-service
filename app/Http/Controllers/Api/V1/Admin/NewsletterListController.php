<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreNewsletterListRequest;
use App\Http\Requests\Api\V1\Admin\UpdateNewsletterListRequest;
use App\Http\Resources\Api\V1\NewsletterListResource;
use App\Models\NewsletterList;
use App\Modules\Newsletter\Services\CreateNewsletterListService;
use App\Modules\Newsletter\Services\DeleteNewsletterListService;
use App\Modules\Newsletter\Services\ListAdminNewsletterListsService;
use App\Modules\Newsletter\Services\UpdateNewsletterListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class NewsletterListController extends Controller
{
    public function index(ListAdminNewsletterListsService $service): AnonymousResourceCollection
    {
        return NewsletterListResource::collection($service->handle());
    }

    public function store(
        StoreNewsletterListRequest $request,
        CreateNewsletterListService $service,
    ): JsonResponse {
        return (new NewsletterListResource($service->handle($request->toData())))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(NewsletterList $newsletterList): NewsletterListResource
    {
        return new NewsletterListResource($newsletterList->loadMissing('subscribers')->loadCount('subscribers'));
    }

    public function update(
        UpdateNewsletterListRequest $request,
        NewsletterList $newsletterList,
        UpdateNewsletterListService $service,
    ): NewsletterListResource {
        return new NewsletterListResource($service->handle($newsletterList, $request->toData()));
    }

    public function destroy(
        NewsletterList $newsletterList,
        DeleteNewsletterListService $service,
    ): Response {
        $service->handle($newsletterList);

        return response()->noContent();
    }
}
