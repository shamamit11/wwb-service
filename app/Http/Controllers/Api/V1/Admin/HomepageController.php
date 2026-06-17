<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateHomepageRequest;
use App\Http\Resources\Api\V1\HomepageResource;
use App\Modules\Homepage\Services\ReadHomepageService;
use App\Modules\Homepage\Services\UpdateHomepageService;
use Illuminate\Http\JsonResponse;

class HomepageController extends Controller
{
    public function show(
        ReadHomepageService $service,
    ): JsonResponse {
        return (new HomepageResource($service->handle()))
            ->response()
            ->setStatusCode(200);
    }

    public function update(
        UpdateHomepageRequest $request,
        UpdateHomepageService $service,
    ): JsonResponse {
        return (new HomepageResource($service->handle($request->toData((int) $request->user()->id))))
            ->response()
            ->setStatusCode(200);
    }
}
