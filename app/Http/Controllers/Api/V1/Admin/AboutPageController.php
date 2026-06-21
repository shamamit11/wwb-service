<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateAboutPageRequest;
use App\Http\Resources\Api\V1\AboutPageResource;
use App\Modules\AboutPage\Services\ReadAboutPageService;
use App\Modules\AboutPage\Services\UpdateAboutPageService;
use Illuminate\Http\JsonResponse;

class AboutPageController extends Controller
{
    public function show(
        ReadAboutPageService $service,
    ): JsonResponse {
        return (new AboutPageResource($service->handle()))
            ->response()
            ->setStatusCode(200);
    }

    public function update(
        UpdateAboutPageRequest $request,
        UpdateAboutPageService $service,
    ): JsonResponse {
        return (new AboutPageResource($service->handle($request->toData((int) $request->user()->id))))
            ->response()
            ->setStatusCode(200);
    }
}
