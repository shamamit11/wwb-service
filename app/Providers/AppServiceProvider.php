<?php

namespace App\Providers;

use App\Models\User;
use App\Modules\Categories\Repositories\CategoryRepository;
use App\Modules\Categories\Repositories\EloquentCategoryRepository;
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
        $this->app->bind(CategoryRepository::class, EloquentCategoryRepository::class);
        $this->app->bind(TagRepository::class, EloquentTagRepository::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-admin-api', fn (User $user): bool => $user->is_admin);
    }
}
