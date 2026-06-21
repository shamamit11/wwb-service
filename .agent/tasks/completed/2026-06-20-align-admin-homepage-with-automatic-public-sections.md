# Task Record

## Task Summary

Align the admin homepage management endpoint with the new automatic public homepage section behavior.

## Requested Outcome

- remove manual curation controls from the admin homepage endpoint for featured editorial, recent articles, and core topics
- keep admin control over section labels, descriptions, and limits where they still apply
- persist homepage section config in a way that matches the new automatic public homepage behavior

## Scope Boundaries

- in scope: service-only admin homepage request/response and persistence changes
- out of scope: sibling admin/frontend app changes
- out of scope: public homepage behavior changes beyond staying compatible with the new admin contract

## Cross-App Reason

- none

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/skills/api-contracts.md`
- `.agent/skills/laravel-api.md`

## Repository Files Inspected

- `app/Http/Requests/Api/V1/Admin/UpdateHomepageRequest.php`
- `app/Http/Resources/Api/V1/HomepageResource.php`
- `app/Models/Homepage.php`
- `app/Modules/Homepage/Data/UpdateHomepageData.php`
- `app/Modules/Homepage/Repositories/EloquentHomepageRepository.php`
- `tests/Feature/HomepageApiTest.php`
- `docs/OPENAPI_SPEC.md`
- `database/seeders/HomepageSeeder.php`
- `app/Modules/Posts/Services/BuildPublicHomeService.php`

## Plan

1. Remove manual curation fields from the admin homepage validation and normalization layer.
2. Update homepage defaults, persistence expectations, tests, and docs to the automatic section contract.
3. Run focused homepage API validation.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Requests/Api/V1/Admin/UpdateHomepageRequest.php`
- `app/Http/Resources/Api/V1/PublicHomeResource.php`
- `app/Models/Homepage.php`
- `app/Modules/Posts/Services/BuildPublicHomeService.php`
- `database/seeders/HomepageSeeder.php`
- `docs/OPENAPI_SPEC.md`
- `tests/Feature/HomepageApiTest.php`
- `tests/Feature/PublicFrontendApiTest.php`

## Validation

- `php artisan test tests/Feature/HomepageApiTest.php tests/Feature/PublicFrontendApiTest.php` passed
- `vendor/bin/pint --test app/Models/Homepage.php app/Http/Requests/Api/V1/Admin/UpdateHomepageRequest.php database/seeders/HomepageSeeder.php docs/OPENAPI_SPEC.md tests/Feature/HomepageApiTest.php app/Modules/Posts/Services/BuildPublicHomeService.php app/Http/Resources/Api/V1/PublicHomeResource.php tests/Feature/PublicFrontendApiTest.php` passed

## Risks Or Follow-Ups

- the admin UI still needs to stop sending the removed manual curation fields if it currently includes them

## Completion Notes

- The admin homepage endpoint no longer accepts manual post/category curation fields for featured editorial, recent articles, or core topics.
- `featured_editorial` and `guide_section` are now stored as automatic sections with editable `title`, `description`, and `limit`.
- `topic_section` now only stores editable `title` and `description`; active categories are resolved automatically on the public homepage.
