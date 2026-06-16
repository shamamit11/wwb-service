<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PublicTagDetailResource;
use App\Http\Resources\Api\V1\PublicTagSummaryResource;
use App\Modules\Tags\Services\FindPublicTagBySlugService;
use App\Modules\Tags\Services\ListPublicTagsService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TagController extends Controller
{
    public function index(ListPublicTagsService $service): AnonymousResourceCollection
    {
        return PublicTagSummaryResource::collection($service->handle());
    }

    public function show(string $slug, FindPublicTagBySlugService $service): PublicTagDetailResource
    {
        return new PublicTagDetailResource($service->handle($slug));
    }
}
