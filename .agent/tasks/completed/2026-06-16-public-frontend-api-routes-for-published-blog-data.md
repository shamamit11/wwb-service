# Current Task

## Task Summary

Add public frontend API routes and supporting services/resources for published blog data.

## Requested Outcome

- expose public category, tag, post, home, search, sitemap, and RSS endpoints under `/api/v1/public`
- ensure responses only include published/public content, active categories, active templates, public-safe media fields, and SEO-safe metadata
- keep the implementation aligned with existing controllers, requests, resources, services, pagination, and testing patterns

## Scope Boundaries

- in scope: read-only public API routes, public query services, public resources, request validation, and public-facing tests
- out of scope: frontend UI work, admin/internal API changes beyond shared published-query hardening, activity logging for reads, and non-service repositories

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/seo.md`
- `.agent/knowledge-base/api-standards.md`
- `.agent/skills/seo.md`
- `.agent/skills/laravel-api.md`

## Repository Files Inspected

- `routes/api.php`
- `config/scramble.php`
- `app/Http/Controllers/Api/V1/CategoryController.php`
- `app/Http/Controllers/Api/V1/Admin/TagController.php`
- `app/Http/Requests/Api/V1/Admin/ListPostsRequest.php`
- `app/Http/Resources/Api/V1/MediaResource.php`
- `app/Http/Resources/Api/V1/TagResource.php`
- `app/Models/Category.php`
- `app/Models/Media.php`
- `app/Models/Post.php`
- `app/Models/Tag.php`
- `app/Models/Template.php`
- `app/Modules/Categories/Services/FindActiveCategoryBySlugService.php`
- `app/Modules/Categories/Services/ListAdminCategoriesService.php`
- `app/Modules/Media/Services/ReadMediaService.php`
- `app/Modules/Posts/Data/PostFiltersData.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Seo/Services/ListRssFeedEntriesService.php`
- `app/Modules/Seo/Services/ListSitemapEntriesService.php`
- `app/Modules/Tags/Repositories/EloquentTagRepository.php`
- `app/Modules/Tags/Repositories/TagRepository.php`
- `app/Modules/Tags/Services/ListAdminTagsService.php`
- `tests/Feature/ApiExceptionHandlingTest.php`
- `tests/Feature/CategoryApiTest.php`

## Plan

1. Add public-safe request DTOs, services, resources, controllers, and routes for category, tag, post, home, search, sitemap, and RSS data.
2. Harden shared published-query behavior so reused sitemap and RSS services also exclude inactive-category content from public outputs.
3. Add focused feature coverage, verify formatting, confirm route and Scramble visibility, and run the full test suite.

## Changed Files

- `app/Http/Controllers/Api/V1/Public/CategoryController.php`
- `app/Http/Controllers/Api/V1/Public/HomeController.php`
- `app/Http/Controllers/Api/V1/Public/PostController.php`
- `app/Http/Controllers/Api/V1/Public/RssController.php`
- `app/Http/Controllers/Api/V1/Public/SearchController.php`
- `app/Http/Controllers/Api/V1/Public/SitemapController.php`
- `app/Http/Controllers/Api/V1/Public/TagController.php`
- `app/Http/Requests/Api/V1/Public/ListPublicPostsRequest.php`
- `app/Http/Requests/Api/V1/Public/SearchPublicPostsRequest.php`
- `app/Http/Resources/Api/V1/PublicCategoryDetailResource.php`
- `app/Http/Resources/Api/V1/PublicCategorySummaryResource.php`
- `app/Http/Resources/Api/V1/PublicHomeResource.php`
- `app/Http/Resources/Api/V1/PublicMediaResource.php`
- `app/Http/Resources/Api/V1/PublicPostBlockResource.php`
- `app/Http/Resources/Api/V1/PublicPostDetailResource.php`
- `app/Http/Resources/Api/V1/PublicPostSummaryResource.php`
- `app/Http/Resources/Api/V1/PublicSeoMetadataResource.php`
- `app/Http/Resources/Api/V1/PublicTagDetailResource.php`
- `app/Http/Resources/Api/V1/PublicTagSummaryResource.php`
- `app/Http/Resources/Api/V1/PublicTemplateResource.php`
- `app/Modules/Categories/Services/FindPublicCategoryBySlugService.php`
- `app/Modules/Categories/Services/ListPublicCategorySummariesService.php`
- `app/Modules/Posts/Data/PublicPostFiltersData.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Services/BuildPublicHomeService.php`
- `app/Modules/Posts/Services/FindPublishedPostBySlugService.php`
- `app/Modules/Posts/Services/ListPublicPostsService.php`
- `app/Modules/Tags/Services/FindPublicTagBySlugService.php`
- `app/Modules/Tags/Services/ListPublicTagsService.php`
- `routes/api.php`
- `tests/Feature/PublicFrontendApiTest.php`

## Validation

- `php artisan test tests/Feature/PublicFrontendApiTest.php tests/Feature/CategoryApiTest.php tests/Feature/RssFeedApiTest.php tests/Feature/SitemapApiTest.php` — passed
- `vendor/bin/pint --test` — passed
- `php artisan route:list --path=api/v1/public` — confirmed 10 public routes
- `php artisan scramble:export --path=<tmp> --silent` plus content check — confirmed exported docs include `/public/categories`, `/public/tags`, `/public/posts`, `/public/home`, `/public/search`, `/public/sitemap`, and `/public/rss`
- `php artisan test` — passed

## Risks Or Follow-Ups

- Public post search now matches category and tag names/slugs in addition to title, slug, and excerpt; if ranking becomes important later, this should move behind a dedicated search strategy rather than accumulating more `LIKE` clauses.
- Sitemap and RSS currently reuse post resources backed by the shared published-post repository query; if future public-feed rules diverge from the generic published-post definition, those services may need their own repository methods.

## Completion Notes

- Added a dedicated public API surface under `/api/v1/public` for categories, tags, posts, home, search, sitemap, and RSS, using public-safe resources that omit admin-only and private-media fields.
- Implemented public list/detail services that only expose published public posts, active categories, and active templates, and return SEO metadata plus schema data for post detail payloads.
- Hardened shared published-post repository queries so sitemap and RSS outputs also exclude inactive-category content, and added end-to-end feature coverage for the new public frontend routes.
