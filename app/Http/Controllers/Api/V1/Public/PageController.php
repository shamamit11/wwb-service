<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PublicPageResource;
use App\Modules\Pages\Services\FindPublicPageBySlugService;

class PageController extends Controller
{
    public function show(string $slug, FindPublicPageBySlugService $service): PublicPageResource
    {
        return new PublicPageResource($service->handle($slug));
    }
}
