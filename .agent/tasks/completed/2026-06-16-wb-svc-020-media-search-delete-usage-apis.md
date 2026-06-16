# Current Task

## Task Summary

Implement media listing, filtering/search, safe deletion, and usage lookup support.

## Requested Outcome

- add media listing support with filtering and search
- expose usage lookup support
- block deletion when usage rules forbid it

## Scope Boundaries

- in scope: admin media list/show/delete behavior, query services, usage services, delete blocking, feature tests, task tracking
- out of scope: media library UI, public media APIs, signed URLs, derivative generation, and reference tracking for content tables that do not exist yet

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
- `.agent/skills/media-service.md`
- `docs/MEDIA_SERVICE.md`
- `docs/DATABASE_DESIGN.md`
- `docs/OPENAPI_SPEC.md`

## Repository Files Inspected

- `app/Http/Controllers/Api/V1/Admin/MediaController.php`
- `app/Http/Resources/Api/V1/MediaResource.php`
- `app/Modules/Media/Repositories/MediaRepository.php`
- `app/Modules/Media/Repositories/EloquentMediaRepository.php`
- `routes/api.php`
- `bootstrap/app.php`

## Plan

1. Extend media listing to support query-based filtering and search through repository/query services.
2. Add usage lookup services that can count and describe references, including future-aware checks for featured media and SEO image usage.
3. Block delete when usage is detected and expose usage details in media responses.
4. Add focused feature tests for listing/filtering/search and safe-delete behavior, then validate the full suite and quality checks.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Admin/MediaController.php`
- `app/Http/Requests/Api/V1/Admin/ListMediaRequest.php`
- `app/Http/Resources/Api/V1/MediaResource.php`
- `app/Modules/Media/Data/MediaFiltersData.php`
- `app/Modules/Media/Data/MediaUsageData.php`
- `app/Modules/Media/Data/MediaUsageReferenceData.php`
- `app/Modules/Media/Exceptions/MediaInUseException.php`
- `app/Modules/Media/Repositories/MediaRepository.php`
- `app/Modules/Media/Repositories/EloquentMediaRepository.php`
- `app/Modules/Media/Services/ListAdminMediaService.php`
- `app/Modules/Media/Services/MediaUsageService.php`
- `app/Modules/Media/Services/SafeDeleteMediaService.php`
- `bootstrap/app.php`
- `tests/Feature/MediaUsageApiTest.php`

## Validation

- `php artisan test tests/Feature/MediaUsageApiTest.php`
- `php artisan route:list --path=media`
- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

## Risks Or Follow-Ups

- Usage checks fully enforce only against tables that exist today; future posts, SEO, or knowledge base tables will activate additional reference checks automatically once their schemas land.

## Completion Notes

- Summary: Added media search and filtering support, usage lookup details in media responses, and safe deletion that blocks when usage rules detect references.
- Changed files: Added media filter and usage DTOs, a `MediaInUseException`, repository search support, list and usage services, a safe-delete service, a list-media request, updated the admin media controller/resource, extended API exception rendering for conflict responses, and added feature coverage in `tests/Feature/MediaUsageApiTest.php`.
- Validation run: `php artisan test tests/Feature/MediaUsageApiTest.php`, `php artisan route:list --path=media`, `php artisan test`, `./vendor/bin/pint --test`, and `./vendor/bin/phpstan analyse` all passed.
- Risks: Current usage enforcement can only check reference tables that actually exist in the repository today, and list filtering by `used` computes usage counts at the application layer rather than in one SQL query.
- Follow-ups: Replace synthetic or future-aware table checks with direct relational queries as posts, SEO metadata, and knowledge base schemas land; consider moving `used` filtering fully into optimized query services later.
