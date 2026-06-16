<?php

use App\Http\Controllers\Api\V1\CreateUserController;
use App\Http\Controllers\Api\V1\EchoMessageController;
use App\Http\Controllers\Api\V1\HealthCheckController;
use App\Http\Controllers\Api\V1\TestErrorController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('health', HealthCheckController::class)
        ->name('api.v1.health');

    Route::post('test/echo', EchoMessageController::class)
        ->name('api.v1.test.echo');

    Route::post('test/users', CreateUserController::class)
        ->name('api.v1.test.users.store');

    Route::get('test/auth', function () {
        return response()->json(['data' => ['authorized' => true]]);
    })->middleware('auth')->name('api.v1.test.auth');

    Route::get('test/users/{user}', function (User $user) {
        return response()->json(['data' => ['id' => $user->id]]);
    })->name('api.v1.test.users.show');

    Route::get('test/error', TestErrorController::class)
        ->name('api.v1.test.error');
});
