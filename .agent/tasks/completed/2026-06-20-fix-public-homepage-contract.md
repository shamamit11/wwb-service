# Task Record

## Task Summary

Fix `GET /api/v1/public/home` so it returns the admin-managed homepage singleton contract instead of the legacy query-driven payload.

## Requested Outcome

- make `api.v1.public.home` return the homepage sections the frontend expects
- ensure the response includes `hero`, `featured_editorial`, `guide_section`, `topic_section`, `promo_section`, `newsletter_section`, and `seo`
- add regression coverage so the public endpoint stays aligned with the homepage singleton shape

## Scope Boundaries

- primary repository remains `service`
- no sibling app reads are required for this backend contract fix
- no admin endpoint changes
- no homepage authoring workflow changes

## Cross-App Reason

- none

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/skills/api-contracts.md`
- `.agent/skills/laravel-api.md`
- `.agent/TESTING.md`
- `.agent/COMMANDS.md`

## Repository Files Inspected

- `routes/api.php`
- `app/Http/Controllers/Api/V1/Public/HomeController.php`
- `app/Http/Resources/Api/V1/PublicHomeResource.php`
- `app/Http/Resources/Api/V1/HomepageResource.php`
- `app/Models/Homepage.php`
- `app/Modules/Posts/Services/BuildPublicHomeService.php`
- `app/Modules/Homepage/Services/ReadHomepageService.php`
- `app/Modules/Homepage/Repositories/HomepageRepository.php`
- `app/Modules/Homepage/Repositories/EloquentHomepageRepository.php`
- `database/seeders/HomepageSeeder.php`
- `tests/Feature/PublicFrontendApiTest.php`
- `tests/Feature/HomepageApiTest.php`
- `docs/OPENAPI_SPEC.md`
- `.agent/tasks/completed/2026-06-17-homepage-management-resource.md`

## Plan

1. Rewire the public home service to read the homepage singleton instead of building a legacy featured/latest/categories payload.
2. Update the public home resource so it emits the full homepage contract shape.
3. Add focused feature coverage and run the narrowest relevant tests.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Public/HomeController.php`
- `app/Http/Resources/Api/V1/PublicHomeResource.php`
- `app/Modules/Posts/Services/BuildPublicHomeService.php`
- `tests/Feature/PublicFrontendApiTest.php`

## Validation

- `php artisan test tests/Feature/PublicFrontendApiTest.php tests/Feature/HomepageApiTest.php` passed
- `vendor/bin/pint --test app/Http/Controllers/Api/V1/Public/HomeController.php app/Modules/Posts/Services/BuildPublicHomeService.php app/Http/Resources/Api/V1/PublicHomeResource.php tests/Feature/PublicFrontendApiTest.php` passed

## Risks Or Follow-Ups

- none

## Completion Notes

- `GET /api/v1/public/home` now reads the homepage singleton instead of returning the legacy `featured_posts/latest_posts/categories` payload.
- The public response now exposes the same section keys as the admin-managed homepage contract: `hero`, `featured_editorial`, `guide_section`, `topic_section`, `promo_section`, `newsletter_section`, and `seo`.
- Added regression coverage for both the configured singleton payload and the auto-bootstrapped default homepage shape on the public endpoint.
