<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SeoScoreResource;
use App\Modules\Seo\Services\ScorePostSeoService;

class SeoScoreController extends Controller
{
    public function show(
        string $seoableType,
        int $seoableId,
        ScorePostSeoService $service,
    ): SeoScoreResource {
        return new SeoScoreResource($service->handle($seoableType, $seoableId));
    }
}
