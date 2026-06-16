<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Modules\Users\Services\CreateUserService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CreateUserController extends Controller
{
    public function __invoke(
        CreateUserRequest $request,
        CreateUserService $service,
    ): JsonResponse {
        $user = $service->handle($request->toData());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
