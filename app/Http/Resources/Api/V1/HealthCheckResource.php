<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class HealthCheckResource extends ApiResource
{
    /**
     * @param  array{status:string,service:string,version:mixed}  $resource
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->resource['status'],
            'service' => $this->resource['service'],
            'version' => $this->resource['version'],
        ];
    }
}
