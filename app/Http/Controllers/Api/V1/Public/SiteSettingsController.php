<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PublicSiteSettingsResource;
use App\Modules\SiteSettings\Services\BuildPublicSiteSettingsService;
use Illuminate\Http\JsonResponse;

class SiteSettingsController extends Controller
{
    public function __invoke(BuildPublicSiteSettingsService $service): JsonResponse
    {
        return (new PublicSiteSettingsResource($service->handle()))
            ->response()
            ->setStatusCode(200);
    }
}
