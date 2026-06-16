<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RssFeedEntryResource;
use App\Modules\Seo\Services\ListRssFeedEntriesService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RssController extends Controller
{
    public function __invoke(ListRssFeedEntriesService $service): AnonymousResourceCollection
    {
        return RssFeedEntryResource::collection($service->handle());
    }
}
