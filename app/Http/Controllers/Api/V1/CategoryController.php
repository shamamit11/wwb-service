<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Modules\Categories\Services\FindActiveCategoryBySlugService;
use App\Modules\Categories\Services\ListPublicCategoriesService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(ListPublicCategoriesService $service): AnonymousResourceCollection
    {
        return CategoryResource::collection($service->handle());
    }

    public function show(
        string $slug,
        FindActiveCategoryBySlugService $service,
    ): CategoryResource {
        return new CategoryResource($service->handle($slug));
    }
}
