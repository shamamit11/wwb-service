<?php

use App\Http\Controllers\Api\V1\Admin\AdminStatusController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Api\V1\Admin\TagController as AdminTagController;
use App\Http\Controllers\Api\V1\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\Api\V1\Auth\AdminLoginController;
use App\Http\Controllers\Api\V1\Auth\AuthenticatedUserController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CreateUserController;
use App\Http\Controllers\Api\V1\EchoMessageController;
use App\Http\Controllers\Api\V1\HealthCheckController;
use App\Http\Controllers\Api\V1\TestErrorController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('health', HealthCheckController::class)
        ->name('api.v1.health');

    Route::prefix('auth')->group(function (): void {
        Route::post('login', AdminLoginController::class)
            ->name('api.v1.auth.login');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('me', AuthenticatedUserController::class)
                ->name('api.v1.auth.me');

            Route::post('logout', LogoutController::class)
                ->name('api.v1.auth.logout');
        });
    });

    Route::prefix('admin')
        ->middleware(['auth:sanctum', 'can:access-admin-api'])
        ->group(function (): void {
            Route::get('me', AdminStatusController::class)
                ->name('api.v1.admin.me');

            Route::apiResource('categories', AdminCategoryController::class)
                ->names('api.v1.admin.categories');

            Route::get('media', [AdminMediaController::class, 'index'])
                ->name('api.v1.admin.media.index');
            Route::post('media', [AdminMediaController::class, 'store'])
                ->name('api.v1.admin.media.store');
            Route::post('media/batch', [AdminMediaController::class, 'batch'])
                ->name('api.v1.admin.media.batch');
            Route::get('media/{media}', [AdminMediaController::class, 'show'])
                ->name('api.v1.admin.media.show');
            Route::put('media/{media}', [AdminMediaController::class, 'update'])
                ->name('api.v1.admin.media.update');
            Route::delete('media/{media}', [AdminMediaController::class, 'destroy'])
                ->name('api.v1.admin.media.destroy');

            Route::apiResource('tags', AdminTagController::class)
                ->names('api.v1.admin.tags');

            Route::apiResource('templates', AdminTemplateController::class)
                ->names('api.v1.admin.templates');
            Route::post('templates/{template}/preview', [AdminTemplateController::class, 'preview'])
                ->name('api.v1.admin.templates.preview');
            Route::post('templates/{template}/seed-post', [AdminTemplateController::class, 'seedPost'])
                ->name('api.v1.admin.templates.seed-post');
        });

    Route::get('categories', [CategoryController::class, 'index'])
        ->name('api.v1.categories.index');

    Route::get('categories/{slug}', [CategoryController::class, 'show'])
        ->name('api.v1.categories.show');

    Route::post('test/echo', EchoMessageController::class)
        ->name('api.v1.test.echo');

    Route::post('test/users', CreateUserController::class)
        ->name('api.v1.test.users.store');

    Route::get('test/auth', function () {
        return response()->json(['data' => ['authorized' => true]]);
    })->middleware('auth:sanctum')->name('api.v1.test.auth');

    Route::get('test/users/{user}', function (User $user) {
        return response()->json(['data' => ['id' => $user->id]]);
    })->name('api.v1.test.users.show');

    Route::get('test/error', TestErrorController::class)
        ->name('api.v1.test.error');
});
