<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PublicHomeResource;
use App\Modules\Posts\Services\BuildPublicHomeService;

class HomeController extends Controller
{
    public function __invoke(BuildPublicHomeService $service): PublicHomeResource
    {
        return new PublicHomeResource($service->handle());
    }
}
