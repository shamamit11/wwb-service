<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PublicCategoryDetailResource;
use App\Http\Resources\Api\V1\PublicCategorySummaryResource;
use App\Modules\Categories\Services\FindPublicCategoryBySlugService;
use App\Modules\Categories\Services\ListPublicCategorySummariesService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(ListPublicCategorySummariesService $service): AnonymousResourceCollection
    {
        return PublicCategorySummaryResource::collection($service->handle());
    }

    public function show(string $slug, FindPublicCategoryBySlugService $service): PublicCategoryDetailResource
    {
        return new PublicCategoryDetailResource($service->handle($slug));
    }
}
