<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\HealthCheckResource;

class HealthCheckController extends Controller
{
    public function __invoke(): HealthCheckResource
    {
        return new HealthCheckResource([
            'status' => 'ok',
            'service' => config('app.name'),
            'version' => config('scramble.info.version'),
        ]);
    }
}
