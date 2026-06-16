# Current Task

## Task Summary

Implement a sitemap data provider and API payload for published content.

## Requested Outcome

- add a sitemap query service
- expose a sitemap endpoint or command-style payload
- ensure sitemap data includes published content only

## Scope Boundaries

- in scope: service-side sitemap query, endpoint payload shape, and tests for published-only inclusion
- out of scope: XML rendering, public frontend consumption, background generation jobs, and non-post content types unless already supported cleanly

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/seo.md`
- `.agent/skills/seo.md`
- `.agent/skills/laravel-api.md`

## Repository Files Inspected

- `routes/api.php`
- `app/Http/Controllers/Api/V1/CategoryController.php`
- `app/Http/Controllers/Api/V1/Admin/PostController.php`
- `app/Http/Resources/Api/ApiResource.php`
- `app/Http/Resources/Api/V1/CategoryResource.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Http/Resources/Api/V1/SeoMetadataResource.php`
- `app/Models/Category.php`
- `app/Models/KnowledgeBaseEntry.php`
- `app/Models/Post.php`
- `app/Modules/Categories/Repositories/CategoryRepository.php`
- `app/Modules/Categories/Repositories/EloquentCategoryRepository.php`
- `app/Modules/Categories/Services/ListPublicCategoriesService.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Seo/Services/CanonicalUrlService.php`
- `tests/Feature/PostApiTest.php`

## Plan

1. Add a sitemap query service that builds deterministic payload items from published public posts.
2. Expose the data through a small API endpoint that reuses the service and resource serialization.
3. Add focused feature coverage, then run targeted tests and the full suite if the change stays cleanly bounded.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Admin/SitemapController.php`
- `app/Http/Resources/Api/V1/SitemapEntryResource.php`
- `app/Modules/Seo/Services/ListSitemapEntriesService.php`
- `routes/api.php`
- `tests/Feature/SitemapApiTest.php`

## Validation

- `php artisan test tests/Feature/SitemapApiTest.php tests/Feature/PostApiTest.php tests/Feature/CanonicalUrlServiceTest.php` — passed
- `vendor/bin/pint --test` — passed
- `php artisan test` — passed

## Risks Or Follow-Ups

- Categories are public but do not have a publish lifecycle, so including them now could violate the published-only acceptance rule unless the product clarifies sitemap scope.

## Completion Notes

- Added `ListSitemapEntriesService` to provide sitemap-ready entries from published public posts only.
- Added an authenticated internal endpoint at `GET /api/v1/admin/seo/sitemap` backed by `SitemapEntryResource`.
- Exposed deterministic sitemap fields for generators: content type, slug, canonical URL, published timestamp, and last-modified timestamp.
- Added feature coverage for auth, published-only filtering, canonical override/default behavior, and ordering.
