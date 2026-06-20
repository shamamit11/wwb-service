# Task Record

## Task Summary

Add the minimal public Pages read path so legal pages can be managed through the existing Pages module and fetched by slug on the public API.

## Requested Outcome

- expose published public pages at `GET /api/v1/public/pages/{slug}`
- keep using the existing generic Pages module for legal pages like Privacy Policy and Terms
- return only public-safe page fields and 404 unpublished/non-public pages

## Scope Boundaries

- in scope: service-only public page endpoint, resource, service, tests, and docs
- out of scope: dedicated Contact page implementation
- out of scope: sibling admin/frontend app changes

## Cross-App Reason

- none

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/skills/api-contracts.md`
- `.agent/skills/laravel-api.md`

## Repository Files Inspected

- `app/Modules/Pages/Repositories/PageRepository.php`
- `app/Modules/Pages/Repositories/EloquentPageRepository.php`
- `app/Http/Resources/Api/V1/PageResource.php`
- `app/Http/Controllers/Api/V1/Public/PostController.php`
- `app/Modules/Posts/Services/FindPublishedPostBySlugService.php`
- `app/Models/Page.php`
- `routes/api.php`
- `tests/Feature/PageApiTest.php`
- `tests/Feature/PublicFrontendApiTest.php`
- `database/seeders/PageSeeder.php`

## Plan

1. Add a public page-by-slug service, controller action, route, and resource.
2. Add focused tests for published public pages and 404 behavior.
3. Update docs and run targeted validation.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Public/PageController.php`
- `app/Http/Resources/Api/V1/PublicPageResource.php`
- `app/Modules/Pages/Services/FindPublicPageBySlugService.php`
- `docs/OPENAPI_SPEC.md`
- `routes/api.php`
- `tests/Feature/PublicFrontendApiTest.php`

## Validation

- `php artisan test tests/Feature/PublicFrontendApiTest.php` passed
- `vendor/bin/pint --test app/Http/Controllers/Api/V1/Public/PageController.php app/Http/Resources/Api/V1/PublicPageResource.php app/Modules/Pages/Services/FindPublicPageBySlugService.php routes/api.php tests/Feature/PublicFrontendApiTest.php` passed
- `php artisan route:list --path=api/v1/public/pages` confirmed the public page route

## Risks Or Follow-Ups

- Contact still needs a dedicated structured page and submission flow rather than reuse of the generic Pages markdown model

## Completion Notes

- Added `GET /api/v1/public/pages/{slug}` for published public pages.
- Legal pages can now be managed through the existing admin Pages APIs and fetched by slug from the public API.
- Draft and non-public pages remain inaccessible on the public endpoint.
