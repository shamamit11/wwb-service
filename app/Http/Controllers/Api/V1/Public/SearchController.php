<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\SearchPublicPostsRequest;
use App\Http\Resources\Api\V1\PublicPostSummaryResource;
use App\Modules\Posts\Services\ListPublicPostsService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchController extends Controller
{
    public function __invoke(
        SearchPublicPostsRequest $request,
        ListPublicPostsService $service,
    ): AnonymousResourceCollection {
        return PublicPostSummaryResource::collection($service->handle($request->toData()));
    }
}
