<?php

namespace App\Providers;

use App\Infrastructure\Ai\Contracts\AiClient;
use App\Infrastructure\Ai\LaravelAiClient;
use App\Models\User;
use App\Modules\AboutPage\Repositories\AboutPageRepository;
use App\Modules\AboutPage\Repositories\EloquentAboutPageRepository;
use App\Modules\Ai\Repositories\AiGenerationStepRepository;
use App\Modules\Ai\Repositories\AiJobCostRepository;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\Ai\Repositories\AiPromptTemplateRepository;
use App\Modules\Ai\Repositories\EloquentAiGenerationStepRepository;
use App\Modules\Ai\Repositories\EloquentAiJobCostRepository;
use App\Modules\Ai\Repositories\EloquentAiJobRepository;
use App\Modules\Ai\Repositories\EloquentAiPromptTemplateRepository;
use App\Modules\Categories\Repositories\CategoryRepository;
use App\Modules\Categories\Repositories\EloquentCategoryRepository;
use App\Modules\ContactPage\Repositories\ContactPageRepository;
use App\Modules\ContactPage\Repositories\ContactSubmissionRepository;
use App\Modules\ContactPage\Repositories\EloquentContactPageRepository;
use App\Modules\ContactPage\Repositories\EloquentContactSubmissionRepository;
use App\Modules\ContentTopics\Repositories\ContentTopicRepository;
use App\Modules\ContentTopics\Repositories\EloquentContentTopicRepository;
use App\Modules\Homepage\Repositories\EloquentHomepageRepository;
use App\Modules\Homepage\Repositories\HomepageRepository;
use App\Modules\KnowledgeBase\Repositories\EloquentKnowledgeBaseEntryRepository;
use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;
use App\Modules\Media\Repositories\EloquentMediaRepository;
use App\Modules\Media\Repositories\MediaRepository;
use App\Modules\Media\Services\Contracts\MediaDeleter;
use App\Modules\Media\Services\Contracts\MediaReader;
use App\Modules\Media\Services\Contracts\MediaStorage;
use App\Modules\Media\Services\Contracts\MediaUploader;
use App\Modules\Media\Services\DeleteMediaService;
use App\Modules\Media\Services\FilesystemMediaStorage;
use App\Modules\Media\Services\ReadMediaService;
use App\Modules\Media\Services\UploadMediaService;
use App\Modules\Newsletter\Contracts\NewsletterDeliveryProvider;
use App\Modules\Newsletter\Providers\MailNewsletterDeliveryProvider;
use App\Modules\Newsletter\Repositories\EloquentNewsletterCampaignRecipientRepository;
use App\Modules\Newsletter\Repositories\EloquentNewsletterCampaignRepository;
use App\Modules\Newsletter\Repositories\EloquentNewsletterListRepository;
use App\Modules\Newsletter\Repositories\EloquentNewsletterRecipientEventRepository;
use App\Modules\Newsletter\Repositories\EloquentNewsletterSubscriberRepository;
use App\Modules\Newsletter\Repositories\NewsletterCampaignRecipientRepository;
use App\Modules\Newsletter\Repositories\NewsletterCampaignRepository;
use App\Modules\Newsletter\Repositories\NewsletterListRepository;
use App\Modules\Newsletter\Repositories\NewsletterRecipientEventRepository;
use App\Modules\Newsletter\Repositories\NewsletterSubscriberRepository;
use App\Modules\Pages\Repositories\EloquentPageRepository;
use App\Modules\Pages\Repositories\PageRepository;
use App\Modules\Posts\Repositories\EloquentPostRepository;
use App\Modules\Posts\Repositories\PostRepository;
use App\Modules\Seo\Repositories\EloquentSeoMetadataRepository;
use App\Modules\Seo\Repositories\SeoMetadataRepository;
use App\Modules\SiteSettings\Repositories\EloquentSiteSettingsRepository;
use App\Modules\SiteSettings\Repositories\SiteSettingsRepository;
use App\Modules\Tags\Repositories\EloquentTagRepository;
use App\Modules\Tags\Repositories\TagRepository;
use App\Modules\Users\Repositories\EloquentUserRepository;
use App\Modules\Users\Repositories\UserRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiClient::class, LaravelAiClient::class);
        $this->app->bind(AboutPageRepository::class, EloquentAboutPageRepository::class);
        $this->app->bind(AiGenerationStepRepository::class, EloquentAiGenerationStepRepository::class);
        $this->app->bind(AiJobRepository::class, EloquentAiJobRepository::class);
        $this->app->bind(AiJobCostRepository::class, EloquentAiJobCostRepository::class);
        $this->app->bind(AiPromptTemplateRepository::class, EloquentAiPromptTemplateRepository::class);
        $this->app->bind(CategoryRepository::class, EloquentCategoryRepository::class);
        $this->app->bind(ContactPageRepository::class, EloquentContactPageRepository::class);
        $this->app->bind(ContactSubmissionRepository::class, EloquentContactSubmissionRepository::class);
        $this->app->bind(ContentTopicRepository::class, EloquentContentTopicRepository::class);
        $this->app->bind(HomepageRepository::class, EloquentHomepageRepository::class);
        $this->app->bind(KnowledgeBaseEntryRepository::class, EloquentKnowledgeBaseEntryRepository::class);
        $this->app->bind(MediaRepository::class, EloquentMediaRepository::class);
        $this->app->bind(MediaStorage::class, FilesystemMediaStorage::class);
        $this->app->bind(MediaUploader::class, UploadMediaService::class);
        $this->app->bind(MediaReader::class, ReadMediaService::class);
        $this->app->bind(MediaDeleter::class, DeleteMediaService::class);
        $this->app->bind(NewsletterSubscriberRepository::class, EloquentNewsletterSubscriberRepository::class);
        $this->app->bind(NewsletterListRepository::class, EloquentNewsletterListRepository::class);
        $this->app->bind(NewsletterCampaignRepository::class, EloquentNewsletterCampaignRepository::class);
        $this->app->bind(NewsletterCampaignRecipientRepository::class, EloquentNewsletterCampaignRecipientRepository::class);
        $this->app->bind(NewsletterRecipientEventRepository::class, EloquentNewsletterRecipientEventRepository::class);
        $this->app->bind(NewsletterDeliveryProvider::class, MailNewsletterDeliveryProvider::class);
        $this->app->bind(PageRepository::class, EloquentPageRepository::class);
        $this->app->bind(PostRepository::class, EloquentPostRepository::class);
        $this->app->bind(SeoMetadataRepository::class, EloquentSeoMetadataRepository::class);
        $this->app->bind(SiteSettingsRepository::class, EloquentSiteSettingsRepository::class);
        $this->app->bind(TagRepository::class, EloquentTagRepository::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! is_dir(storage_path('framework/views'))) {
            mkdir(storage_path('framework/views'), 0755, true);
        }

        Gate::define('access-admin-api', fn (User $user): bool => $user->is_admin);
    }
}
