# Current Task

## Task Summary

Implement a singleton admin homepage management resource with dedicated persistence, validation, resource output, OpenAPI coverage, and tests.

## Requested Outcome

- Add admin `GET /admin/homepage` and `PUT /admin/homepage` endpoints.
- Persist homepage content in a dedicated structured store, not generic settings.
- Follow existing Laravel service architecture, auth, validation, resource, and docs conventions.
- Add feature and validation coverage, including default/bootstrap and ordering preservation behavior.

## Scope Boundaries

- In scope: service-only backend/API changes for homepage admin management.
- In scope: migration, model, repository, DTO, service, request, controller, resource, routes, OpenAPI docs, and tests.
- Out of scope: admin UI implementation in sibling apps.
- Out of scope: generic settings management or page-builder behavior.

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/api-standards.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/api-contracts.md`
- `.agent/TESTING.md`
- `.agent/COMMANDS.md`
- `.agent/skills/database.md`

## Repository Files Inspected

- `routes/api.php`
- `app/Providers/AppServiceProvider.php`
- `app/Http/Controllers/Api/V1/Admin/SeoMetadataController.php`
- `app/Http/Requests/Api/V1/Admin/UpdateSeoMetadataRequest.php`
- `app/Http/Resources/Api/V1/SeoMetadataResource.php`
- `app/Http/Controllers/Api/V1/Admin/PageController.php`
- `app/Http/Requests/Api/V1/Admin/StorePageRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdatePageRequest.php`
- `app/Http/Resources/Api/V1/PageResource.php`
- `app/Modules/Posts/Services/BuildPublicHomeService.php`
- `app/Http/Resources/Api/V1/PublicHomeResource.php`
- `app/Http/Controllers/Api/V1/Public/HomeController.php`
- `app/Models/Post.php`
- `app/Models/Page.php`
- `app/Models/Category.php`
- `app/Models/User.php`
- `app/Modules/Pages/Services/CreatePageService.php`
- `app/Modules/Pages/Services/UpdatePageService.php`
- `app/Modules/Pages/Repositories/PageRepository.php`
- `app/Modules/Pages/Repositories/EloquentPageRepository.php`
- `app/Modules/Pages/Data/CreatePageData.php`
- `app/Modules/Pages/Data/UpdatePageData.php`
- `app/Modules/Seo/Services/UpsertSeoMetadataService.php`
- `app/Modules/Seo/Repositories/SeoMetadataRepository.php`
- `app/Modules/Seo/Repositories/EloquentSeoMetadataRepository.php`
- `app/Support/AuditActivityLogger.php`
- `database/migrations/2026_06_17_120000_create_pages_table.php`
- `database/migrations/2026_06_16_193000_create_seo_metadata_table.php`
- `tests/Feature/AdminApiAuthTest.php`
- `tests/Feature/PageApiTest.php`
- `tests/Feature/PublicFrontendApiTest.php`
- `docs/OPENAPI_SPEC.md`

## Plan

1. Inspect existing admin singleton/resource, validation, persistence, and OpenAPI patterns.
2. Implement homepage persistence, service flow, admin endpoints, validation, and resource formatting.
3. Add targeted tests, run focused validation, and record results plus residual risks.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Providers/AppServiceProvider.php`
- `routes/api.php`
- `docs/OPENAPI_SPEC.md`
- `app/Http/Controllers/Api/V1/Admin/HomepageController.php`
- `app/Http/Requests/Api/V1/Admin/UpdateHomepageRequest.php`
- `app/Http/Resources/Api/V1/HomepageResource.php`
- `app/Models/Homepage.php`
- `app/Modules/Homepage/Data/UpdateHomepageData.php`
- `app/Modules/Homepage/Repositories/HomepageRepository.php`
- `app/Modules/Homepage/Repositories/EloquentHomepageRepository.php`
- `app/Modules/Homepage/Services/ReadHomepageService.php`
- `app/Modules/Homepage/Services/UpdateHomepageService.php`
- `database/migrations/2026_06_17_210354_create_homepages_table.php`
- `tests/Feature/HomepageApiTest.php`

## Validation

- `php artisan test --filter=HomepageApiTest` -> passed
- `php artisan test --filter='AdminApiAuthTest|HomepageApiTest'` -> passed
- `./vendor/bin/pint --dirty` -> passed
- Re-ran `php artisan test --filter='AdminApiAuthTest|HomepageApiTest'` after formatting -> passed

## Risks Or Follow-Ups

- Public frontend homepage consumption still uses the existing query-driven home service. This task adds the admin-managed singleton resource and its persistence contract, but does not yet rewire the public homepage endpoint to consume it.

## Completion Notes

- Added a dedicated `Homepage` singleton module with dedicated persistence and admin-only `GET`/`PUT` endpoints.
- The singleton auto-bootstraps on first `GET` with a full default shape and stores ordered arrays exactly as submitted on update.
- Featured editorial and guide sections support both `manual` and `automatic` modes; no drag-and-drop or generic settings model was introduced.
