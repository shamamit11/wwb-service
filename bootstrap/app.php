<?php

use App\Modules\Ai\Exceptions\AiJobRetryNotAllowedException;
use App\Modules\ContentBriefs\Exceptions\ContentBriefGenerationNotAllowedException;
use App\Modules\ContentBriefs\Exceptions\InvalidContentBriefStateTransitionException;
use App\Modules\ContentTopics\Exceptions\DuplicateContentTopicException;
use App\Modules\ContentTopics\Exceptions\InvalidContentTopicStateTransitionException;
use App\Modules\Media\Exceptions\MediaInUseException;
use App\Modules\Newsletter\Exceptions\NewsletterCampaignSendNotAllowedException;
use App\Modules\Posts\Exceptions\BlogDraftGenerationNotAllowedException;
use App\Modules\Posts\Exceptions\InvalidPostStateTransitionException;
use App\Support\ApiErrorResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                $exception->getMessage(),
                'VALIDATION_ERROR',
                $exception->status,
                $exception->errors()
            );
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                $exception->getMessage(),
                'UNAUTHORIZED',
                401
            );
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                $exception->getMessage() ?: 'This action is unauthorized.',
                'FORBIDDEN',
                403
            );
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                'Resource not found.',
                'NOT_FOUND',
                404
            );
        });

        $exceptions->render(function (ThrottleRequestsException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                $exception->getMessage() ?: 'Too many requests.',
                'RATE_LIMITED',
                429
            );
        });

        $exceptions->render(function (MediaInUseException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                $exception->getMessage(),
                'CONFLICT',
                409,
                [
                    'usage' => array_map(
                        static fn ($reference): array => $reference->toArray(),
                        $exception->usage->references,
                    ),
                ],
                [
                    'usage_count' => $exception->usage->usageCount,
                ],
            );
        });

        $exceptions->render(function (InvalidPostStateTransitionException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                $exception->getMessage(),
                'CONFLICT',
                409,
                [
                    'status' => [$exception->currentStatus],
                    'action' => [$exception->action],
                ],
            );
        });

        $exceptions->render(function (AiJobRetryNotAllowedException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                $exception->getMessage(),
                'CONFLICT',
                409,
                [
                    'status' => [$exception->currentStatus],
                ],
            );
        });

        $exceptions->render(function (DuplicateContentTopicException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                $exception->getMessage(),
                'CONFLICT',
                409,
                [
                    'title' => [$exception->title],
                    'cluster' => [$exception->cluster],
                ],
            );
        });

        $exceptions->render(function (ContentBriefGenerationNotAllowedException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                $exception->getMessage(),
                'CONFLICT',
                409,
                [
                    'status' => [$exception->topicStatus],
                    'action' => ['generate-brief'],
                ],
            );
        });

        $exceptions->render(function (BlogDraftGenerationNotAllowedException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                $exception->getMessage(),
                'CONFLICT',
                409,
                [
                    'status' => [$exception->briefStatus],
                    'action' => ['generate-draft'],
                ],
            );
        });

        $exceptions->render(function (NewsletterCampaignSendNotAllowedException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                $exception->getMessage(),
                'CONFLICT',
                409,
                [
                    'status' => [$exception->campaignStatus],
                ],
            );
        });

        $exceptions->render(function (InvalidContentBriefStateTransitionException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                $exception->getMessage(),
                'CONFLICT',
                409,
                [
                    'status' => [$exception->currentStatus],
                    'action' => [$exception->action],
                ],
            );
        });

        $exceptions->render(function (InvalidContentTopicStateTransitionException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                $request,
                $exception->getMessage(),
                'CONFLICT',
                409,
                [
                    'status' => [$exception->currentStatus],
                    'action' => [$exception->action],
                ],
            );
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($exception instanceof HttpExceptionInterface) {
                return ApiErrorResponse::make(
                    $request,
                    $exception->getMessage() ?: 'Request failed.',
                    'HTTP_ERROR',
                    $exception->getStatusCode()
                );
            }

            return ApiErrorResponse::fromThrowable($request, $exception);
        });
    })->create();
