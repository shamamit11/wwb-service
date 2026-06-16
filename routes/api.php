<?php

use App\Http\Controllers\Api\V1\Admin\AdminStatusController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\KnowledgeBaseEntryController as AdminKnowledgeBaseEntryController;
use App\Http\Controllers\Api\V1\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Api\V1\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\V1\Admin\RssFeedController as AdminRssFeedController;
use App\Http\Controllers\Api\V1\Admin\SchemaController as AdminSchemaController;
use App\Http\Controllers\Api\V1\Admin\SeoMetadataController as AdminSeoMetadataController;
use App\Http\Controllers\Api\V1\Admin\SeoScoreController as AdminSeoScoreController;
use App\Http\Controllers\Api\V1\Admin\SitemapController as AdminSitemapController;
use App\Http\Controllers\Api\V1\Admin\TagController as AdminTagController;
use App\Http\Controllers\Api\V1\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\Api\V1\Auth\AdminLoginController;
use App\Http\Controllers\Api\V1\Auth\AuthenticatedUserController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CreateUserController;
use App\Http\Controllers\Api\V1\EchoMessageController;
use App\Http\Controllers\Api\V1\HealthCheckController;
use App\Http\Controllers\Api\V1\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\V1\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Api\V1\Public\PostController as PublicPostController;
use App\Http\Controllers\Api\V1\Public\RssController as PublicRssController;
use App\Http\Controllers\Api\V1\Public\SearchController as PublicSearchController;
use App\Http\Controllers\Api\V1\Public\SitemapController as PublicSitemapController;
use App\Http\Controllers\Api\V1\Public\TagController as PublicTagController;
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

            Route::apiResource('knowledge-base', AdminKnowledgeBaseEntryController::class)
                ->parameters(['knowledge-base' => 'knowledgeBase'])
                ->names('api.v1.admin.knowledge-base');
            Route::post('knowledge-base/{knowledgeBase}/link-post', [AdminKnowledgeBaseEntryController::class, 'linkPost'])
                ->name('api.v1.admin.knowledge-base.link-post');
            Route::post('knowledge-base/{knowledgeBase}/link-topic', [AdminKnowledgeBaseEntryController::class, 'linkTopic'])
                ->name('api.v1.admin.knowledge-base.link-topic');

            Route::apiResource('posts', AdminPostController::class)
                ->names('api.v1.admin.posts');
            Route::post('posts/{post}/publish', [AdminPostController::class, 'publish'])
                ->name('api.v1.admin.posts.publish');
            Route::post('posts/{post}/schedule', [AdminPostController::class, 'schedule'])
                ->name('api.v1.admin.posts.schedule');
            Route::post('posts/{post}/unpublish', [AdminPostController::class, 'unpublish'])
                ->name('api.v1.admin.posts.unpublish');

            Route::get('seo/schema/{seoableType}/{seoableId}', [AdminSchemaController::class, 'show'])
                ->name('api.v1.admin.seo.schema.show');
            Route::get('seo/score/{seoableType}/{seoableId}', [AdminSeoScoreController::class, 'show'])
                ->name('api.v1.admin.seo.score.show');
            Route::get('seo/{seoableType}/{seoableId}', [AdminSeoMetadataController::class, 'show'])
                ->name('api.v1.admin.seo.show');
            Route::put('seo/{seoableType}/{seoableId}', [AdminSeoMetadataController::class, 'update'])
                ->name('api.v1.admin.seo.update');
            Route::get('seo/sitemap', AdminSitemapController::class)
                ->name('api.v1.admin.seo.sitemap');
            Route::get('feeds/rss', AdminRssFeedController::class)
                ->name('api.v1.admin.feeds.rss');

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

    Route::prefix('public')->group(function (): void {
        Route::get('categories', [PublicCategoryController::class, 'index'])
            ->name('api.v1.public.categories.index');
        Route::get('categories/{slug}', [PublicCategoryController::class, 'show'])
            ->name('api.v1.public.categories.show');
        Route::get('tags', [PublicTagController::class, 'index'])
            ->name('api.v1.public.tags.index');
        Route::get('tags/{slug}', [PublicTagController::class, 'show'])
            ->name('api.v1.public.tags.show');
        Route::get('posts', [PublicPostController::class, 'index'])
            ->name('api.v1.public.posts.index');
        Route::get('posts/{slug}', [PublicPostController::class, 'show'])
            ->name('api.v1.public.posts.show');
        Route::get('home', PublicHomeController::class)
            ->name('api.v1.public.home');
        Route::get('search', PublicSearchController::class)
            ->name('api.v1.public.search');
        Route::get('sitemap', PublicSitemapController::class)
            ->name('api.v1.public.sitemap');
        Route::get('rss', PublicRssController::class)
            ->name('api.v1.public.rss');
    });

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
