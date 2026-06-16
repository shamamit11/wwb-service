# Current Task

## Task Summary

Implement CRUD APIs for posts and structured block payloads.

## Requested Outcome

- add post controllers
- add requests
- add resources
- add filtering and sorting support

## Scope Boundaries

- in scope: admin post CRUD endpoints, list filters/sorts, request validation, DTO conversion, API resources, routes, and tests
- out of scope: publish/schedule/unpublish endpoints, public post endpoints, SEO resources, sibling repositories, and frontend/admin UI work

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/ARCHITECTURE.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/api-contracts.md`
- `.agent/skills/database.md`
- `docs/OPENAPI_SPEC.md`
- `docs/DATABASE_DESIGN.md`

## Repository Files Inspected

- `routes/api.php`
- `app/Http/Controllers/Api/V1/Admin/TemplateController.php`
- `app/Http/Controllers/Api/V1/Admin/MediaController.php`
- `app/Http/Controllers/Api/V1/Admin/PostController.php`
- `app/Http/Resources/Api/V1/TemplateResource.php`
- `app/Http/Resources/Api/V1/MediaResource.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Http/Resources/Api/V1/PostBlockResource.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Services/CreatePostService.php`
- `app/Modules/Posts/Services/UpdatePostService.php`
- `app/Modules/Posts/Services/DeletePostService.php`
- `app/Modules/Posts/Services/ListAdminPostsService.php`
- `tests/Feature/TemplateApiTest.php`
- `tests/Feature/CategoryApiTest.php`
- `tests/Feature/MediaApiTest.php`
- `tests/Feature/PostCommandServiceTest.php`

## Plan

1. Add post list-filter DTOs and service support so admin listing can filter and sort without pushing query logic into controllers.
2. Add form requests that validate post payloads and map them into the existing post command DTOs and block payload DTOs.
3. Add post resources for posts, blocks, and nested related summaries, then wire the admin controller and routes.
4. Add focused feature coverage for auth, CRUD, validation, filtering, and sorting, then validate with the test suite and quality checks.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Admin/PostController.php`
- `app/Http/Requests/Api/V1/Admin/Concerns/InteractsWithPostData.php`
- `app/Http/Requests/Api/V1/Admin/ListPostsRequest.php`
- `app/Http/Requests/Api/V1/Admin/StorePostRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdatePostRequest.php`
- `app/Http/Resources/Api/V1/PostBlockResource.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Modules/Posts/Data/PostFiltersData.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Services/ListAdminPostsService.php`
- `routes/api.php`
- `tests/Feature/PostApiTest.php`

## Validation

- `php artisan test tests/Feature/PostApiTest.php`
- `php artisan test tests/Feature/PostCommandServiceTest.php`
- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

## Risks Or Follow-Ups

- Public post list/detail endpoints are intentionally deferred, but the list/filter layer should stay reusable for those later APIs.
- Admin list responses currently return full block and relation payloads for each post to stay contract-complete; if list size grows materially, add pagination and a lighter index resource instead of pushing ad hoc trimming into controllers.

## Completion Notes

- Summary: Added authenticated admin CRUD endpoints for posts, request-to-DTO mapping for structured block payloads, reusable admin list filtering/sorting, API resources for posts and blocks, and feature coverage for auth, CRUD, validation, and list filters.
- Changed files: post controller, requests, resources, repository filter support, list service, admin routes, and `tests/Feature/PostApiTest.php`.
- Validation run: post feature tests, post command service tests, full `php artisan test`, Pint, and PHPStan all passed.
- Risks: public-safe post APIs, pagination, and separate lightweight index/detail resource splits remain deferred by scope.
- Follow-ups: if the admin consumer needs pagination metadata next, extend the list service/resource contract rather than adding repository-specific controller conditionals.
