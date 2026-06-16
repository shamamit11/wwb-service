# Current Task

## Task Summary

Implement the auth and admin API support foundation for admin/API consumers using Sanctum.

## Requested Outcome

- add auth endpoint support
- protect admin service routes
- add user role or permission checks for admin access
- use Sanctum for API authentication

## Scope Boundaries

- in scope: Sanctum installation/configuration, token auth endpoints, admin route protection, role/permission baseline, auth feature tests, task tracking
- out of scope: admin UI screens, sibling repositories, full RBAC package adoption, and non-auth domain APIs

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
- `.agent/knowledge-base/api-standards.md`
- `docs/OPENAPI_SPEC.md`

## Repository Files Inspected

- `composer.json`
- `bootstrap/app.php`
- `routes/api.php`
- `app/Models/User.php`
- `app/Support/ApiErrorResponse.php`
- `config/auth.php`
- `app/Http/Resources/Api/V1/UserResource.php`
- `tests/Feature/ExampleTest.php`

## Plan

1. Inspect the current authentication baseline and install Sanctum using the narrowest Laravel-supported path.
2. Add a simple admin access foundation based on authenticated users plus an explicit admin flag or equivalent authorization gate.
3. Implement token login/logout/me endpoints and a protected admin route surface using Sanctum and consistent JSON errors.
4. Add feature tests for unauthorized, forbidden, and authenticated admin flows, then record validation results.

## Changed Files

- `.agent/tasks/current-task.md`
- `composer.json`
- `composer.lock`
- `config/sanctum.php`
- `app/Http/Controllers/Api/V1/Admin/AdminStatusController.php`
- `app/Http/Controllers/Api/V1/Auth/AdminLoginController.php`
- `app/Http/Controllers/Api/V1/Auth/AuthenticatedUserController.php`
- `app/Http/Controllers/Api/V1/Auth/LogoutController.php`
- `app/Http/Requests/Api/V1/Auth/AdminLoginRequest.php`
- `app/Http/Resources/Api/V1/Auth/AdminAccessTokenResource.php`
- `app/Http/Resources/Api/V1/UserResource.php`
- `app/Models/User.php`
- `app/Modules/Auth/Data/AdminLoginData.php`
- `app/Modules/Auth/Services/IssueAdminApiTokenService.php`
- `app/Modules/Users/Repositories/EloquentUserRepository.php`
- `app/Modules/Users/Repositories/UserRepository.php`
- `app/Providers/AppServiceProvider.php`
- `database/migrations/2026_06_16_162231_create_personal_access_tokens_table.php`
- `database/migrations/2026_06_16_162500_add_is_admin_to_users_table.php`
- `routes/api.php`
- `tests/Feature/AdminApiAuthTest.php`
- `tests/Feature/ExampleTest.php`

## Validation

- `php artisan test tests/Feature/AdminApiAuthTest.php`
- `php artisan route:list --path=api/v1/auth`
- `php artisan route:list --path=api/v1/admin`
- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

## Risks Or Follow-Ups

- A minimal `is_admin` flag is sufficient for Phase 1, but the project will likely need a more granular role or permission model once more admin capabilities are added.

## Completion Notes

- Summary: Installed Sanctum, added token-based admin auth endpoints, protected admin API routes with `auth:sanctum` plus an admin gate, and added a simple `is_admin` authorization baseline on the `users` table and model.
- Changed files: Added Sanctum package/config/migration, new auth controllers/request/resource/service/DTO classes, extended the user repository with email lookup, updated `User` to support API tokens and admin casting, added the admin gate in `AppServiceProvider`, protected routes in `routes/api.php`, and converted the leftover default feature test to a service-health assertion.
- Validation run: `php artisan test tests/Feature/AdminApiAuthTest.php`, `php artisan route:list --path=api/v1/auth`, `php artisan route:list --path=api/v1/admin`, `php artisan test`, `./vendor/bin/pint --test`, and `./vendor/bin/phpstan analyse` all passed.
- Risks: The login flow currently issues admin-capable tokens only and intentionally avoids introducing a broader permissions package in this phase.
- Follow-ups: Add token expiration/pruning policy, expand role granularity beyond `is_admin`, and align future admin content endpoints to the same `auth:sanctum` plus authorization gate pattern.
