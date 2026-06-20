<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PublicAboutPageResource;
use App\Modules\AboutPage\Services\BuildPublicAboutPageService;
use Illuminate\Http\JsonResponse;

class AboutController extends Controller
{
    public function __invoke(BuildPublicAboutPageService $service): JsonResponse
    {
        return (new PublicAboutPageResource($service->handle()))
            ->response()
            ->setStatusCode(200);
    }
}
