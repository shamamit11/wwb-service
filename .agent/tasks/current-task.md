# Current Task

## Task Summary

Implement publish, schedule, and unpublish services and admin API endpoints for post lifecycle transitions.

## Requested Outcome

- add publish service
- add schedule service
- add unpublish service
- add admin endpoints

## Scope Boundaries

- in scope: explicit post lifecycle transition services, request validation, controller endpoints, routes, domain error handling, and tests
- out of scope: public post endpoints, scheduler jobs that execute scheduled publishes, sibling repositories, and frontend/admin UI work

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/api-standards.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/api-contracts.md`
- `docs/OPENAPI_SPEC.md`
- `docs/DATABASE_DESIGN.md`

## Repository Files Inspected

- `bootstrap/app.php`
- `routes/api.php`
- `app/Models/Post.php`
- `app/Http/Controllers/Api/V1/Admin/PostController.php`
- `app/Http/Requests/Api/V1/Admin/ListPostsRequest.php`
- `app/Http/Requests/Api/V1/Admin/StorePostRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdatePostRequest.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Services/CreatePostService.php`
- `app/Modules/Posts/Services/UpdatePostService.php`
- `app/Support/ApiErrorResponse.php`
- `tests/Feature/PostApiTest.php`
- `tests/Feature/PostCommandServiceTest.php`
- `tests/Feature/PostRepositoryTest.php`
- `tests/Feature/MediaUsageApiTest.php`

## Plan

1. Add a small transition DTO, repository transition write path, and a post-state exception for blocked lifecycle changes.
2. Implement explicit publish, schedule, and unpublish services with narrow transition rules and timestamp handling.
3. Add admin transition endpoints, request validation for scheduling, and route wiring that returns the standard `PostResource`.
4. Add focused service and API coverage for valid transitions, invalid transitions, and stored future schedule intent, then run targeted and full test validation.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Admin/PostController.php`
- `app/Http/Requests/Api/V1/Admin/PublishPostRequest.php`
- `app/Http/Requests/Api/V1/Admin/SchedulePostRequest.php`
- `app/Http/Requests/Api/V1/Admin/UnpublishPostRequest.php`
- `app/Modules/Posts/Data/PostStateTransitionData.php`
- `app/Modules/Posts/Data/SchedulePostData.php`
- `app/Modules/Posts/Exceptions/InvalidPostStateTransitionException.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Services/PublishPostService.php`
- `app/Modules/Posts/Services/SchedulePostService.php`
- `app/Modules/Posts/Services/UnpublishPostService.php`
- `bootstrap/app.php`
- `routes/api.php`
- `tests/Feature/PostApiTest.php`
- `tests/Feature/PostCommandServiceTest.php`

## Validation

- `php artisan test tests/Feature/PostCommandServiceTest.php`
- `php artisan test tests/Feature/PostApiTest.php`
- `vendor/bin/pint --test`
- `php artisan test`

## Risks Or Follow-Ups

- The task covers storing schedule intent only; actual background execution of scheduled publishes remains a separate workflow.

## Completion Notes

- Summary: Added explicit publish, schedule, and unpublish services for posts, wired admin transition endpoints, validated schedule payloads, and mapped invalid lifecycle changes to API `409 CONFLICT` responses.
- Validation run: targeted post service tests, targeted post API tests, Pint, and full `php artisan test` all passed.
- Risks: scheduled posts now store future publish intent correctly, but automatic execution of scheduled publishes still needs a separate scheduler or job workflow.
