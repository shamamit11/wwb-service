<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateSeoMetadataRequest;
use App\Http\Resources\Api\V1\SeoMetadataResource;
use App\Modules\Seo\Services\ReadSeoMetadataService;
use App\Modules\Seo\Services\UpsertSeoMetadataService;
use Illuminate\Http\JsonResponse;

class SeoMetadataController extends Controller
{
    public function show(
        string $seoableType,
        int $seoableId,
        ReadSeoMetadataService $service,
    ): SeoMetadataResource {
        return new SeoMetadataResource($service->handle($seoableType, $seoableId)->loadMissing('ogImageMedia'));
    }

    public function update(
        UpdateSeoMetadataRequest $request,
        string $seoableType,
        int $seoableId,
        UpsertSeoMetadataService $service,
    ): JsonResponse {
        return (new SeoMetadataResource(
            $service->handle($seoableType, $seoableId, $request->toData()),
        ))->response()->setStatusCode(200);
    }
}
