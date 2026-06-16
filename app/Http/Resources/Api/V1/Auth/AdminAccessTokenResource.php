<?php

namespace App\Http\Resources\Api\V1\Auth;

use App\Http\Resources\Api\ApiResource;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\Request;

class AdminAccessTokenResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->resource['token'],
            'token_type' => $this->resource['token_type'],
            'abilities' => $this->resource['abilities'],
            'user' => UserResource::make($this->resource['user'])->resolve($request),
        ];
    }
}
