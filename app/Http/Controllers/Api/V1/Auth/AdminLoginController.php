<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\AdminLoginRequest;
use App\Http\Resources\Api\V1\Auth\AdminAccessTokenResource;
use App\Modules\Auth\Services\IssueAdminApiTokenService;

class AdminLoginController extends Controller
{
    public function __invoke(
        AdminLoginRequest $request,
        IssueAdminApiTokenService $service,
    ): AdminAccessTokenResource {
        return new AdminAccessTokenResource($service->handle($request->toData()));
    }
}
