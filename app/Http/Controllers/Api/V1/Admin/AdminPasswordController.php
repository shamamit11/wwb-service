<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\ChangeAdminPasswordRequest;
use App\Modules\Auth\Services\ChangeAdminPasswordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPasswordController extends Controller
{
    public function __invoke(
        ChangeAdminPasswordRequest $request,
        ChangeAdminPasswordService $service,
    ): JsonResponse {
        $service->handle($request->user(), $request->toData());

        return response()->json([
            'data' => [
                'password_changed' => true,
            ],
        ]);
    }
}
