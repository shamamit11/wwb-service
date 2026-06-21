<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateSiteSettingsRequest;
use App\Http\Resources\Api\V1\SiteSettingsResource;
use App\Modules\SiteSettings\Services\ReadSiteSettingsService;
use App\Modules\SiteSettings\Services\UpdateSiteSettingsService;
use Illuminate\Http\JsonResponse;

class SiteSettingsController extends Controller
{
    public function show(ReadSiteSettingsService $service): JsonResponse
    {
        return (new SiteSettingsResource($service->handle()))
            ->response()
            ->setStatusCode(200);
    }

    public function update(
        UpdateSiteSettingsRequest $request,
        UpdateSiteSettingsService $service,
    ): JsonResponse {
        return (new SiteSettingsResource($service->handle($request->toData((int) $request->user()->id))))
            ->response()
            ->setStatusCode(200);
    }
}
