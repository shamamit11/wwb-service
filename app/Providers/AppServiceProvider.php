<?php

namespace App\Providers;

use App\Infrastructure\Ai\Contracts\AiClient;
use App\Infrastructure\Ai\LaravelAiClient;
use App\Models\User;
use App\Modules\Ai\Repositories\AiGenerationStepRepository;
use App\Modules\Ai\Repositories\AiJobCostRepository;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\Ai\Repositories\EloquentAiGenerationStepRepository;
use App\Modules\Ai\Repositories\EloquentAiJobCostRepository;
use App\Modules\Ai\Repositories\EloquentAiJobRepository;
use App\Modules\Categories\Repositories\CategoryRepository;
use App\Modules\Categories\Repositories\EloquentCategoryRepository;
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
use App\Modules\Pages\Repositories\EloquentPageRepository;
use App\Modules\Pages\Repositories\PageRepository;
use App\Modules\Posts\Repositories\EloquentPostBlockRepository;
use App\Modules\Posts\Repositories\EloquentPostRepository;
use App\Modules\Posts\Repositories\PostBlockRepository;
use App\Modules\Posts\Repositories\PostRepository;
use App\Modules\Seo\Repositories\EloquentSeoMetadataRepository;
use App\Modules\Seo\Repositories\SeoMetadataRepository;
use App\Modules\Tags\Repositories\EloquentTagRepository;
use App\Modules\Tags\Repositories\TagRepository;
use App\Modules\Templates\Repositories\EloquentTemplateRepository;
use App\Modules\Templates\Repositories\TemplateRepository;
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
        $this->app->bind(AiGenerationStepRepository::class, EloquentAiGenerationStepRepository::class);
        $this->app->bind(AiJobRepository::class, EloquentAiJobRepository::class);
        $this->app->bind(AiJobCostRepository::class, EloquentAiJobCostRepository::class);
        $this->app->bind(CategoryRepository::class, EloquentCategoryRepository::class);
        $this->app->bind(HomepageRepository::class, EloquentHomepageRepository::class);
        $this->app->bind(KnowledgeBaseEntryRepository::class, EloquentKnowledgeBaseEntryRepository::class);
        $this->app->bind(MediaRepository::class, EloquentMediaRepository::class);
        $this->app->bind(MediaStorage::class, FilesystemMediaStorage::class);
        $this->app->bind(MediaUploader::class, UploadMediaService::class);
        $this->app->bind(MediaReader::class, ReadMediaService::class);
        $this->app->bind(MediaDeleter::class, DeleteMediaService::class);
        $this->app->bind(PageRepository::class, EloquentPageRepository::class);
        $this->app->bind(PostBlockRepository::class, EloquentPostBlockRepository::class);
        $this->app->bind(PostRepository::class, EloquentPostRepository::class);
        $this->app->bind(SeoMetadataRepository::class, EloquentSeoMetadataRepository::class);
        $this->app->bind(TemplateRepository::class, EloquentTemplateRepository::class);
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
