# Current Task

## Task Summary

Establish controller, request, DTO, service, repository, and resource conventions for the Laravel service layer.

## Requested Outcome

- create a baseline directory structure for layered backend features
- define reusable DTO and repository conventions
- provide API resource conventions aligned with the existing JSON envelope
- prove the pattern with at least one sample request flow

## Scope Boundaries

- in scope: service-only Laravel architecture scaffolding, sample flow wiring, tests, and task tracking
- out of scope: sibling repositories, full domain module implementation, UI scaffolding, and broad refactors unrelated to the baseline pattern

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/database.md`
- `.agent/knowledge-base/api-standards.md`
- `docs/OPENAPI_SPEC.md`

## Repository Files Inspected

- `bootstrap/app.php`
- `routes/api.php`
- `app/Http/Controllers/Api/V1/HealthCheckController.php`
- `app/Http/Controllers/Api/V1/EchoMessageController.php`
- `app/Http/Requests/Api/V1/EchoMessageRequest.php`
- `app/Http/Resources/Api/ApiResource.php`
- `app/Support/ApiErrorResponse.php`
- `app/Providers/AppServiceProvider.php`
- `app/Models/User.php`
- `database/migrations/0001_01_01_000000_create_users_table.php`
- `database/factories/UserFactory.php`
- `tests/Feature/ApiExceptionHandlingTest.php`

## Plan

1. Inspect the current API controller, request, and resource baseline to identify the smallest usable sample flow to refactor.
2. Add base abstractions and directory structure for DTOs, services, repositories, and module-oriented feature code.
3. Refactor one existing sample endpoint to follow `Controller -> FormRequest -> DTO -> Service -> Repository -> Resource`.
4. Add or update tests proving the layered flow works and record validation results.

## Changed Files

- `.agent/tasks/current-task.md`
- `.gitignore`
- `app/Http/Controllers/Api/V1/CreateUserController.php`
- `app/Http/Requests/Api/V1/CreateUserRequest.php`
- `app/Http/Resources/Api/V1/HealthCheckResource.php`
- `app/Http/Resources/Api/V1/UserResource.php`
- `app/Modules/README.md`
- `app/Modules/Shared/Data/DataTransferObject.php`
- `app/Modules/Users/Data/CreateUserData.php`
- `app/Modules/Users/Repositories/EloquentUserRepository.php`
- `app/Modules/Users/Repositories/UserRepository.php`
- `app/Modules/Users/Services/CreateUserService.php`
- `app/Providers/AppServiceProvider.php`
- `routes/api.php`
- `tests/Feature/LayeredArchitectureTest.php`

## Validation

- `php artisan test tests/Feature/LayeredArchitectureTest.php`
- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

## Risks Or Follow-Ups

- The sample flow proves the layered structure and persistence boundary, but real admin content modules will still need module-specific conventions for policies, pagination, and multi-write transactions.

## Completion Notes

- Summary: Added a baseline `app/Modules` convention scaffold, a shared DTO base class, repository binding conventions, and a sample `CreateUser` flow wired through request, DTO, service, repository, and resource layers.
- Changed files: Added new controller/request/resource/module classes, bound the repository contract in `AppServiceProvider`, added a sample route under `api/v1/test/users`, updated `.gitignore` for Larastan cache output, and fixed a stale PHPDoc issue in `HealthCheckResource`.
- Validation run: `php artisan test tests/Feature/LayeredArchitectureTest.php`, `php artisan test`, `./vendor/bin/pint --test`, and `./vendor/bin/phpstan analyse` all passed.
- Risks: The sample route is intentionally a proof-of-structure endpoint and may be replaced by the first real admin content module later.
- Follow-ups: Reuse the same folder split for categories, posts, media, and SEO modules; add policy and pagination conventions as those modules are introduced.
