# Task Record

## Task Summary

Fix page publish validation so public pages cannot be saved in a `published` state without a `published_at` timestamp.

## Requested Outcome

- Investigate why `GET /api/v1/public/pages/privacy-policy` returned `404`.
- Prevent admin/API page writes from saving `status = published` with `published_at = null`.

## Scope Boundaries

- In scope: service-side investigation, page request validation, feature coverage, and API contract docs.
- Out of scope: frontend admin fixes, data backfill for existing invalid rows, and broader page workflow refactors.

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/skills/laravel-api.md`
- `.agent/TESTING.md`

## Repository Files Inspected

- `routes/api.php`
- `app/Http/Controllers/Api/V1/Public/PageController.php`
- `app/Modules/Pages/Services/FindPublicPageBySlugService.php`
- `app/Modules/Pages/Repositories/EloquentPageRepository.php`
- `app/Http/Requests/Api/V1/Admin/StorePageRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdatePageRequest.php`
- `app/Http/Controllers/Api/V1/Admin/PageController.php`
- `app/Http/Resources/Api/V1/PageResource.php`
- `app/Modules/Pages/Services/CreatePageService.php`
- `app/Modules/Pages/Services/UpdatePageService.php`
- `app/Modules/Seo/Services/CanonicalUrlService.php`
- `tests/Feature/PublicFrontendApiTest.php`
- `tests/Feature/PageApiTest.php`
- `docs/OPENAPI_SPEC.md`

## Plan

1. Confirm the runtime cause of the public-page `404`.
2. Tighten page write validation for published pages.
3. Add focused regression tests and validate the change.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Requests/Api/V1/Admin/StorePageRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdatePageRequest.php`
- `tests/Feature/PageApiTest.php`
- `docs/OPENAPI_SPEC.md`

## Validation

- Passed: `php artisan test tests/Feature/PageApiTest.php`
- Passed: `vendor/bin/pint --test app/Http/Requests/Api/V1/Admin/StorePageRequest.php app/Http/Requests/Api/V1/Admin/UpdatePageRequest.php tests/Feature/PageApiTest.php`

## Risks Or Follow-Ups

- Existing invalid page rows, including `privacy-policy` in the local environment, still need a data fix or backfill.
- `CanonicalUrlService` still treats pages as public based on `status` and `visibility` only; that behavior was not changed here.

## Completion Notes

- The `404` was caused by `pages.slug = privacy-policy` having `published_at = null`.
- Page create/update requests now require `published_at` whenever `status = published`.
- Regression tests now cover both create and update validation for this case.
