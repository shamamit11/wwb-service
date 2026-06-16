<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\ListPublicPostsRequest;
use App\Http\Resources\Api\V1\PublicPostDetailResource;
use App\Http\Resources\Api\V1\PublicPostSummaryResource;
use App\Modules\Posts\Services\FindPublishedPostBySlugService;
use App\Modules\Posts\Services\ListPublicPostsService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function index(
        ListPublicPostsRequest $request,
        ListPublicPostsService $service,
    ): AnonymousResourceCollection {
        return PublicPostSummaryResource::collection($service->handle($request->toData()));
    }

    public function show(string $slug, FindPublishedPostBySlugService $service): PublicPostDetailResource
    {
        return new PublicPostDetailResource($service->handle($slug));
    }
}
