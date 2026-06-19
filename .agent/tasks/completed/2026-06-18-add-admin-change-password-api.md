# Task Summary

Add an admin-only API endpoint that lets the authenticated admin change their own password.

## Requested Outcome

- add a protected admin password change endpoint
- require the current password plus confirmed new password
- keep the implementation inside the standard request -> DTO -> service pattern

## Scope Boundaries

- in scope: route, controller, request, DTO, service, and feature tests
- out of scope: user-management APIs for changing other users' passwords and sibling app changes

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/api-standards.md`
- `.agent/skills/laravel-api.md`

## Repository Files Inspected

- `routes/api.php`
- `app/Http/Controllers/Api/V1/Auth/AdminLoginController.php`
- `app/Http/Controllers/Api/V1/Auth/AuthenticatedUserController.php`
- `app/Http/Controllers/Api/V1/Auth/LogoutController.php`
- `app/Http/Requests/Api/V1/Auth/AdminLoginRequest.php`
- `app/Http/Requests/Api/V1/CreateUserRequest.php`
- `app/Modules/Auth/Data/AdminLoginData.php`
- `app/Modules/Auth/Services/IssueAdminApiTokenService.php`
- `app/Models/User.php`
- `tests/Feature/AdminApiAuthTest.php`

## Plan

1. Add an admin-scoped password change request, DTO, service, controller, and route.
2. Add feature coverage for authentication, success, and validation/current-password failures.
3. Run focused auth tests and then the full test suite.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Modules/Auth/Data/ChangePasswordData.php`
- `app/Modules/Auth/Services/ChangeAdminPasswordService.php`
- `app/Http/Requests/Api/V1/Admin/ChangeAdminPasswordRequest.php`
- `app/Http/Controllers/Api/V1/Admin/AdminPasswordController.php`
- `routes/api.php`
- `tests/Feature/AdminApiAuthTest.php`

## Validation

- `php artisan test tests/Feature/AdminApiAuthTest.php`
- `php artisan test`

## Risks Or Follow-Ups

- This endpoint will change the currently authenticated admin's password only; changing another user's password would need a separate admin user-management contract.

## Completion Notes

- Added `POST /api/v1/admin/change-password` behind the existing admin auth middleware.
- The endpoint changes the currently authenticated admin's password only and requires `current_password`, `password`, and `password_confirmation`.
- Password updates flow through a request DTO and auth service, and return `{ "data": { "password_changed": true } }` on success.
