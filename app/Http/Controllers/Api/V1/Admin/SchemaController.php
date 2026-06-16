<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SchemaPayloadResource;
use App\Modules\Seo\Services\GenerateSchemaPayloadService;

class SchemaController extends Controller
{
    public function show(
        string $seoableType,
        int $seoableId,
        GenerateSchemaPayloadService $service,
    ): SchemaPayloadResource {
        return new SchemaPayloadResource($service->handle($seoableType, $seoableId));
    }
}
