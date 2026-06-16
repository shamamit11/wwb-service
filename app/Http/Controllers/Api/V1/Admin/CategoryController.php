<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreCategoryRequest;
use App\Http\Requests\Api\V1\Admin\UpdateCategoryRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use App\Modules\Categories\Services\CreateCategoryService;
use App\Modules\Categories\Services\DeleteCategoryService;
use App\Modules\Categories\Services\ListAdminCategoriesService;
use App\Modules\Categories\Services\UpdateCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function index(ListAdminCategoriesService $service): AnonymousResourceCollection
    {
        return CategoryResource::collection($service->handle());
    }

    public function store(
        StoreCategoryRequest $request,
        CreateCategoryService $service,
    ): JsonResponse {
        $category = $service->handle(
            $request->toData((int) $request->user()->id),
        );

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        UpdateCategoryService $service,
    ): CategoryResource {
        return new CategoryResource(
            $service->handle($category, $request->toData((int) $request->user()->id)),
        );
    }

    public function destroy(
        Category $category,
        DeleteCategoryService $service,
    ): Response {
        $service->handle($category);

        return response()->noContent();
    }
}
