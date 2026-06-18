<?php

use App\Http\Controllers\Api\V1\Admin\AdminStatusController;
use App\Http\Controllers\Api\V1\Admin\AdminPasswordController;
use App\Http\Controllers\Api\V1\Admin\AiJobController as AdminAiJobController;
use App\Http\Controllers\Api\V1\Admin\AiPromptTemplateController as AdminAiPromptTemplateController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\ContentBriefController as AdminContentBriefController;
use App\Http\Controllers\Api\V1\Admin\ContentTopicController as AdminContentTopicController;
use App\Http\Controllers\Api\V1\Admin\HomepageController as AdminHomepageController;
use App\Http\Controllers\Api\V1\Admin\KnowledgeBaseEntryController as AdminKnowledgeBaseEntryController;
use App\Http\Controllers\Api\V1\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Api\V1\Admin\NewsletterCampaignController as AdminNewsletterCampaignController;
use App\Http\Controllers\Api\V1\Admin\NewsletterListController as AdminNewsletterListController;
use App\Http\Controllers\Api\V1\Admin\NewsletterSubscriberController as AdminNewsletterSubscriberController;
use App\Http\Controllers\Api\V1\Admin\PageController as AdminPageController;
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
use App\Http\Controllers\Api\V1\Public\NewsletterController as PublicNewsletterController;
use App\Http\Controllers\Api\V1\Public\NewsletterTrackingController as PublicNewsletterTrackingController;
use App\Http\Controllers\Api\V1\Public\NewsletterWebhookController as PublicNewsletterWebhookController;
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
            Route::post('change-password', AdminPasswordController::class)
                ->name('api.v1.admin.change-password');

            Route::get('ai-jobs', [AdminAiJobController::class, 'index'])
                ->name('api.v1.admin.ai-jobs.index');
            Route::post('ai-jobs/topic-discovery', [AdminAiJobController::class, 'queueTopicDiscovery'])
                ->name('api.v1.admin.ai-jobs.topic-discovery');
            Route::get('ai-jobs/{aiJob}', [AdminAiJobController::class, 'show'])
                ->name('api.v1.admin.ai-jobs.show');
            Route::post('ai-jobs/{aiJob}/retry', [AdminAiJobController::class, 'retry'])
                ->name('api.v1.admin.ai-jobs.retry');
            Route::get('ai-prompts', [AdminAiPromptTemplateController::class, 'index'])
                ->name('api.v1.admin.ai-prompts.index');
            Route::post('ai-prompts', [AdminAiPromptTemplateController::class, 'store'])
                ->name('api.v1.admin.ai-prompts.store');
            Route::get('ai-prompts/{aiPrompt}', [AdminAiPromptTemplateController::class, 'show'])
                ->name('api.v1.admin.ai-prompts.show');
            Route::patch('ai-prompts/{aiPrompt}', [AdminAiPromptTemplateController::class, 'update'])
                ->name('api.v1.admin.ai-prompts.update');
            Route::post('ai-prompts/{aiPrompt}/versions', [AdminAiPromptTemplateController::class, 'storeVersion'])
                ->name('api.v1.admin.ai-prompts.versions.store');
            Route::post('ai-prompts/{aiPrompt}/activate-version/{versionId}', [AdminAiPromptTemplateController::class, 'activateVersion'])
                ->name('api.v1.admin.ai-prompts.versions.activate');
            Route::get('content-topics', [AdminContentTopicController::class, 'index'])
                ->name('api.v1.admin.content-topics.index');
            Route::post('content-topics', [AdminContentTopicController::class, 'store'])
                ->name('api.v1.admin.content-topics.store');
            Route::get('content-topics/{contentTopic}', [AdminContentTopicController::class, 'show'])
                ->name('api.v1.admin.content-topics.show');
            Route::patch('content-topics/{contentTopic}', [AdminContentTopicController::class, 'update'])
                ->name('api.v1.admin.content-topics.update');
            Route::delete('content-topics/{contentTopic}', [AdminContentTopicController::class, 'destroy'])
                ->name('api.v1.admin.content-topics.destroy');
            Route::post('content-topics/{contentTopic}/generate-brief', [AdminContentTopicController::class, 'generateBrief'])
                ->name('api.v1.admin.content-topics.generate-brief');
            Route::post('content-topics/{contentTopic}/approve', [AdminContentTopicController::class, 'approve'])
                ->name('api.v1.admin.content-topics.approve');
            Route::post('content-topics/{contentTopic}/reject', [AdminContentTopicController::class, 'reject'])
                ->name('api.v1.admin.content-topics.reject');
            Route::post('content-topics/{contentTopic}/mark-used', [AdminContentTopicController::class, 'markUsed'])
                ->name('api.v1.admin.content-topics.mark-used');
            Route::get('content-briefs', [AdminContentBriefController::class, 'index'])
                ->name('api.v1.admin.content-briefs.index');
            Route::get('content-briefs/{contentBrief}', [AdminContentBriefController::class, 'show'])
                ->name('api.v1.admin.content-briefs.show');
            Route::patch('content-briefs/{contentBrief}', [AdminContentBriefController::class, 'update'])
                ->name('api.v1.admin.content-briefs.update');
            Route::post('content-briefs/{contentBrief}/approve', [AdminContentBriefController::class, 'approve'])
                ->name('api.v1.admin.content-briefs.approve');
            Route::post('content-briefs/{contentBrief}/generate-draft', [AdminContentBriefController::class, 'generateDraft'])
                ->name('api.v1.admin.content-briefs.generate-draft');

            Route::get('homepage', [AdminHomepageController::class, 'show'])
                ->name('api.v1.admin.homepage.show');
            Route::put('homepage', [AdminHomepageController::class, 'update'])
                ->name('api.v1.admin.homepage.update');

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

            Route::prefix('newsletter')->group(function (): void {
                Route::apiResource('lists', AdminNewsletterListController::class)
                    ->parameters(['lists' => 'newsletterList'])
                    ->names('api.v1.admin.newsletter.lists');

                Route::get('subscribers', [AdminNewsletterSubscriberController::class, 'index'])
                    ->name('api.v1.admin.newsletter.subscribers.index');
                Route::post('subscribers', [AdminNewsletterSubscriberController::class, 'store'])
                    ->name('api.v1.admin.newsletter.subscribers.store');
                Route::get('subscribers/{newsletterSubscriber}', [AdminNewsletterSubscriberController::class, 'show'])
                    ->name('api.v1.admin.newsletter.subscribers.show');
                Route::patch('subscribers/{newsletterSubscriber}', [AdminNewsletterSubscriberController::class, 'update'])
                    ->name('api.v1.admin.newsletter.subscribers.update');
                Route::post('subscribers/{newsletterSubscriber}/unsubscribe', [AdminNewsletterSubscriberController::class, 'unsubscribe'])
                    ->name('api.v1.admin.newsletter.subscribers.unsubscribe');
                Route::post('subscribers/{newsletterSubscriber}/resubscribe', [AdminNewsletterSubscriberController::class, 'resubscribe'])
                    ->name('api.v1.admin.newsletter.subscribers.resubscribe');

                Route::get('campaigns', [AdminNewsletterCampaignController::class, 'index'])
                    ->name('api.v1.admin.newsletter.campaigns.index');
                Route::post('campaigns', [AdminNewsletterCampaignController::class, 'store'])
                    ->name('api.v1.admin.newsletter.campaigns.store');
                Route::get('campaigns/{newsletterCampaign}', [AdminNewsletterCampaignController::class, 'show'])
                    ->name('api.v1.admin.newsletter.campaigns.show');
                Route::patch('campaigns/{newsletterCampaign}', [AdminNewsletterCampaignController::class, 'update'])
                    ->name('api.v1.admin.newsletter.campaigns.update');
                Route::delete('campaigns/{newsletterCampaign}', [AdminNewsletterCampaignController::class, 'destroy'])
                    ->name('api.v1.admin.newsletter.campaigns.destroy');
                Route::get('campaigns/{newsletterCampaign}/recipients', [AdminNewsletterCampaignController::class, 'recipients'])
                    ->name('api.v1.admin.newsletter.campaigns.recipients.index');
                Route::post('campaigns/{newsletterCampaign}/stage-recipients', [AdminNewsletterCampaignController::class, 'stageRecipients'])
                    ->name('api.v1.admin.newsletter.campaigns.recipients.stage');
                Route::post('campaigns/{newsletterCampaign}/send', [AdminNewsletterCampaignController::class, 'send'])
                    ->name('api.v1.admin.newsletter.campaigns.send');
            });

            Route::apiResource('posts', AdminPostController::class)
                ->names('api.v1.admin.posts');
            Route::post('posts/{post}/publish', [AdminPostController::class, 'publish'])
                ->name('api.v1.admin.posts.publish');
            Route::post('posts/{post}/schedule', [AdminPostController::class, 'schedule'])
                ->name('api.v1.admin.posts.schedule');
            Route::post('posts/{post}/unpublish', [AdminPostController::class, 'unpublish'])
                ->name('api.v1.admin.posts.unpublish');

            Route::apiResource('pages', AdminPageController::class)
                ->names('api.v1.admin.pages');

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
        Route::post('newsletter/subscribe', [PublicNewsletterController::class, 'subscribe'])
            ->name('api.v1.public.newsletter.subscribe');
        Route::match(['get', 'post'], 'newsletter/unsubscribe', [PublicNewsletterController::class, 'unsubscribe'])
            ->name('api.v1.public.newsletter.unsubscribe');
        Route::get('newsletter/track/open/{recipient}', [PublicNewsletterTrackingController::class, 'open'])
            ->name('api.v1.public.newsletter.track.open');
        Route::get('newsletter/track/click/{recipient}/{target}', [PublicNewsletterTrackingController::class, 'click'])
            ->name('api.v1.public.newsletter.track.click');
        Route::post('newsletter/webhooks/events', PublicNewsletterWebhookController::class)
            ->name('api.v1.public.newsletter.webhooks.events');
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
