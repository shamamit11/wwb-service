# Task Summary

Implement `AV-SVC-1.3` for Article Videos by adding repository and query support for recommendations, videos, and video memory entries.

## Requested Outcome

- add repository interfaces and Eloquent implementations for the new Article Video models
- support core lookup helpers needed by the next workflow tasks
- add idempotent pending-recommendation upsert behavior for recommendation refreshes
- validate repository behavior with focused tests

## Scope Boundaries

- in scope: repository interfaces, Eloquent implementations, supporting DTOs, container bindings, focused repository tests
- out of scope: AI workflows, commands, API endpoints, Admin UI, render logic

## Assumptions

- repository methods should cover the queries explicitly called out by the backlog: active draft lookup, rendered-video existence, recent memory fetches, and pending recommendation refreshes
- create and update methods are worth adding now so later service tasks can build on the same repository layer instead of bypassing it
- Article Video repository code should live under `App\Modules\ArticleVideos\...` as the feature-scoped counterpart to the new models and enums

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/skills/database.md`
- `.agent/TESTING.md`
- `.agent/COMMANDS.md`

## Repository Files Inspected

- `app/Providers/AppServiceProvider.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Media/Repositories/MediaRepository.php`
- `app/Modules/Media/Repositories/EloquentMediaRepository.php`
- `app/Modules/Categories/Data/CreateCategoryData.php`
- `app/Modules/Media/Data/CreateMediaData.php`
- `tests/Feature/CategoryRepositoryTest.php`
- `tests/Feature/KnowledgeBaseEntryRepositoryTest.php`

## Plan

1. Add Article Video DTOs for create/update and pending-recommendation refresh flows.
2. Add repository interfaces and Eloquent implementations for recommendations, videos, and memory entries.
3. Bind the new repositories in `AppServiceProvider`.
4. Add focused repository tests covering upsert, active-draft lookup, rendered-video existence, and recent memory fetches.
5. Run targeted validation and formatting.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Providers/AppServiceProvider.php`
- `app/Modules/ArticleVideos/Data/CreateArticleVideoRecommendationData.php`
- `app/Modules/ArticleVideos/Data/UpdateArticleVideoRecommendationData.php`
- `app/Modules/ArticleVideos/Data/RefreshPendingArticleVideoRecommendationData.php`
- `app/Modules/ArticleVideos/Data/CreateArticleVideoData.php`
- `app/Modules/ArticleVideos/Data/UpdateArticleVideoData.php`
- `app/Modules/ArticleVideos/Data/CreateArticleVideoMemoryEntryData.php`
- `app/Modules/ArticleVideos/Repositories/ArticleVideoRecommendationRepository.php`
- `app/Modules/ArticleVideos/Repositories/ArticleVideoRepository.php`
- `app/Modules/ArticleVideos/Repositories/ArticleVideoMemoryEntryRepository.php`
- `app/Modules/ArticleVideos/Repositories/EloquentArticleVideoRecommendationRepository.php`
- `app/Modules/ArticleVideos/Repositories/EloquentArticleVideoRepository.php`
- `app/Modules/ArticleVideos/Repositories/EloquentArticleVideoMemoryEntryRepository.php`
- `tests/Feature/ArticleVideoRepositoryTest.php`

## Validation

- `php -l app/Providers/AppServiceProvider.php && php -l app/Modules/ArticleVideos/Data/CreateArticleVideoRecommendationData.php && php -l app/Modules/ArticleVideos/Data/UpdateArticleVideoRecommendationData.php && php -l app/Modules/ArticleVideos/Data/RefreshPendingArticleVideoRecommendationData.php && php -l app/Modules/ArticleVideos/Data/CreateArticleVideoData.php && php -l app/Modules/ArticleVideos/Data/UpdateArticleVideoData.php && php -l app/Modules/ArticleVideos/Data/CreateArticleVideoMemoryEntryData.php && php -l app/Modules/ArticleVideos/Repositories/ArticleVideoRecommendationRepository.php && php -l app/Modules/ArticleVideos/Repositories/ArticleVideoRepository.php && php -l app/Modules/ArticleVideos/Repositories/ArticleVideoMemoryEntryRepository.php && php -l app/Modules/ArticleVideos/Repositories/EloquentArticleVideoRecommendationRepository.php && php -l app/Modules/ArticleVideos/Repositories/EloquentArticleVideoRepository.php && php -l app/Modules/ArticleVideos/Repositories/EloquentArticleVideoMemoryEntryRepository.php && php -l tests/Feature/ArticleVideoRepositoryTest.php`
- `DB_CONNECTION=sqlite DB_DATABASE=/Users/amitsharma/Herd/widewebblog/service/database/database.sqlite php artisan test tests/Feature/ArticleVideoRepositoryTest.php`
- `vendor/bin/pint app/Providers/AppServiceProvider.php app/Modules/ArticleVideos/Data/CreateArticleVideoRecommendationData.php app/Modules/ArticleVideos/Data/UpdateArticleVideoRecommendationData.php app/Modules/ArticleVideos/Data/RefreshPendingArticleVideoRecommendationData.php app/Modules/ArticleVideos/Data/CreateArticleVideoData.php app/Modules/ArticleVideos/Data/UpdateArticleVideoData.php app/Modules/ArticleVideos/Data/CreateArticleVideoMemoryEntryData.php app/Modules/ArticleVideos/Repositories/ArticleVideoRecommendationRepository.php app/Modules/ArticleVideos/Repositories/ArticleVideoRepository.php app/Modules/ArticleVideos/Repositories/ArticleVideoMemoryEntryRepository.php app/Modules/ArticleVideos/Repositories/EloquentArticleVideoRecommendationRepository.php app/Modules/ArticleVideos/Repositories/EloquentArticleVideoRepository.php app/Modules/ArticleVideos/Repositories/EloquentArticleVideoMemoryEntryRepository.php tests/Feature/ArticleVideoRepositoryTest.php`

## Risks Or Follow-Ups

- repository search/filter methods for admin listing can stay out of this task until endpoint work requires them
- recommendation refresh upsert currently updates the latest pending row for a post; if future rules allow multiple concurrent pending variants, the keying strategy will need to change

## Completion Notes

- added feature-scoped DTOs for Article Video recommendation, video, and memory-entry persistence flows
- added repository interfaces and Eloquent implementations for recommendations, videos, and memory entries under `App\Modules\ArticleVideos\Repositories`
- wired the new repositories into `AppServiceProvider`
- added focused repository coverage for pending recommendation upsert, active draft lookup, rendered-video existence, and recent memory ordering
