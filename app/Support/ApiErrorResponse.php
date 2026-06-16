<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiErrorResponse
{
    /**
     * @param  array<string, mixed>  $errors
     * @param  array<string, mixed>  $meta
     */
    public static function make(
        Request $request,
        string $message,
        string $errorCode,
        int $status,
        array $errors = [],
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'message' => $message,
            'error_code' => $errorCode,
            'errors' => $errors === [] ? (object) [] : $errors,
            'meta' => array_merge([
                'request_id' => self::requestId($request),
            ], $meta),
        ], $status);
    }

    public static function fromThrowable(Request $request, Throwable $throwable): JsonResponse
    {
        return self::make(
            $request,
            'An unexpected error occurred.',
            'INTERNAL_ERROR',
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    private static function requestId(Request $request): string
    {
        return (string) ($request->headers->get('X-Request-Id') ?: $request->attributes->get('request_id') ?: str()->ulid());
    }
}
