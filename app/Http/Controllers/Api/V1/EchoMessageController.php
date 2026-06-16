<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EchoMessageRequest;
use Illuminate\Http\JsonResponse;

class EchoMessageController extends Controller
{
    public function __invoke(EchoMessageRequest $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'message' => $request->validated('message'),
            ],
        ]);
    }
}
