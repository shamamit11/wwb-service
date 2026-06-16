# Current Task

## Task Summary

Implement canonical URL derivation and expose it through SEO and published content payloads.

## Requested Outcome

- add canonical generation service
- add content-type aware URL derivation

## Scope Boundaries

- in scope: canonical URL service, override-aware default derivation, SEO read-path integration, and published content resource exposure
- out of scope: sitemap generation, frontend rendering changes, scoring logic, and public post endpoint implementation

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/TESTING.md`
- `.agent/skills/seo.md`
- `.agent/knowledge-base/seo.md`

## Repository Files Inspected

- `app/Http/Controllers/Api/V1/CategoryController.php`
- `app/Http/Resources/Api/V1/CategoryResource.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Http/Resources/Api/V1/SeoMetadataResource.php`
- `app/Modules/Categories/Repositories/EloquentCategoryRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Seo/Services/ReadSeoMetadataService.php`
- `tests/Feature/CategoryApiTest.php`
- `tests/Feature/SeoMetadataApiTest.php`

## Plan

1. Add a canonical URL service that respects explicit SEO overrides and derives defaults by content type.
2. Integrate that service into SEO read responses and published content serializers.
3. Add focused tests for override/default behavior and for canonical availability on published content payloads.
4. Validate with targeted tests, Pint, and the full suite, then archive the task note.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Resources/Api/V1/CategoryResource.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Modules/Categories/Repositories/EloquentCategoryRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Seo/Services/CanonicalUrlService.php`
- `app/Modules/Seo/Services/ReadSeoMetadataService.php`
- `tests/Feature/CanonicalUrlServiceTest.php`
- `tests/Feature/SeoMetadataApiTest.php`

## Validation

- `php artisan test tests/Feature/CanonicalUrlServiceTest.php tests/Feature/SeoMetadataApiTest.php tests/Feature/CategoryApiTest.php` — passed
- `vendor/bin/pint --test` — passed
- `php artisan test` — passed

## Risks Or Follow-Ups

- Public post endpoints are not implemented yet, so published-post payload coverage will be validated through resource serialization rather than an external API route.

## Completion Notes

- Added `CanonicalUrlService` to resolve canonical URLs by content type while respecting explicit SEO overrides.
- Exposed derived canonical values through SEO read responses and top-level post/category API resources for eligible published or active content.
- Ensured repositories eager load `seo` where needed so canonical resolution does not incur missing-relation behavior in serializers.
- Added feature coverage for default derivation, override behavior, and published resource exposure.
