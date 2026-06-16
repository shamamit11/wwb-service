# Current Task

## Task Summary

Implement service-side schema payload generation and serialization for SEO consumers.

## Requested Outcome

- add schema builders
- add payload serializers
- ensure schema payloads can be generated from service data without UI logic

## Scope Boundaries

- in scope: schema graph builders for posts and categories, internal schema payload endpoint, and service-side FAQ extraction from stored post blocks
- out of scope: public frontend rendering, XML or HTML serialization, and full article-body rendering

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

- `app/Enums/ContentBlockType.php`
- `app/Http/Controllers/Api/V1/Admin/SeoMetadataController.php`
- `app/Http/Requests/Api/V1/Admin/UpdateSeoMetadataRequest.php`
- `app/Http/Resources/Api/V1/CategoryResource.php`
- `app/Http/Resources/Api/V1/PostBlockResource.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Models/PostBlock.php`
- `app/Models/SeoMetadata.php`
- `app/Modules/Posts/Data/CreatePostBlockData.php`
- `app/Modules/Posts/Repositories/EloquentPostBlockRepository.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Seo/Data/UpdateSeoMetadataData.php`
- `app/Modules/Seo/Repositories/EloquentSeoMetadataRepository.php`
- `app/Modules/Seo/Services/ResolveSeoableTargetService.php`
- `app/Modules/Seo/Services/UpsertSeoMetadataService.php`
- `app/Support/ApiErrorResponse.php`
- `tests/Feature/PostCommandServiceTest.php`
- `tests/Feature/SeoMetadataApiTest.php`

## Plan

1. Add schema builders for organization, website, breadcrumb, article/category page, and FAQ payloads.
2. Compose those builders behind a service and expose a thin internal schema endpoint with a resource serializer.
3. Add focused schema tests, then validate with targeted tests, Pint, and the full suite.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Admin/SchemaController.php`
- `app/Http/Resources/Api/V1/SchemaPayloadResource.php`
- `app/Modules/Seo/Schema/ArticleSchemaBuilder.php`
- `app/Modules/Seo/Schema/BreadcrumbSchemaBuilder.php`
- `app/Modules/Seo/Schema/CategorySchemaBuilder.php`
- `app/Modules/Seo/Schema/FaqSchemaBuilder.php`
- `app/Modules/Seo/Schema/OrganizationSchemaBuilder.php`
- `app/Modules/Seo/Schema/WebsiteSchemaBuilder.php`
- `app/Modules/Seo/Services/GenerateSchemaPayloadService.php`
- `routes/api.php`
- `tests/Feature/SchemaDataApiTest.php`

## Validation

- `php artisan test tests/Feature/SchemaDataApiTest.php tests/Feature/SeoMetadataApiTest.php tests/Feature/PostApiTest.php` — passed
- `vendor/bin/pint --test` — passed
- `php artisan test` — passed

## Risks Or Follow-Ups

- Schema generation stays data-only for now; if public feeds or pages need rendered article HTML in schema, that should come from a dedicated rendering service rather than these builders.

## Completion Notes

- Added service-side schema builders under `app/Modules/Seo/Schema/` for `Organization`, `WebSite`, `BreadcrumbList`, `Article`, `CollectionPage`, and `FAQPage`.
- Added `GenerateSchemaPayloadService` to build graph payloads for posts and categories without UI logic and to honor stored `schema_type` / `schema_payload` overrides.
- Added an authenticated internal endpoint at `GET /api/v1/admin/seo/schema/{seoableType}/{seoableId}` backed by `SchemaPayloadResource`.
- Added feature coverage for auth, post graph generation, FAQ extraction, category graph generation, and schema override merging.
