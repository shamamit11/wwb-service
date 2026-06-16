<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SitemapEntryResource;
use App\Modules\Seo\Services\ListSitemapEntriesService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SitemapController extends Controller
{
    public function __invoke(ListSitemapEntriesService $service): AnonymousResourceCollection
    {
        return SitemapEntryResource::collection($service->handle());
    }
}
