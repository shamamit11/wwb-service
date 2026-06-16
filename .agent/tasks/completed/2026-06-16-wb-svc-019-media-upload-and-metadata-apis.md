# Current Task

## Task Summary

Implement media upload, batch upload, and metadata APIs on top of the existing media storage abstractions.

## Requested Outcome

- add a single upload endpoint
- add a batch upload endpoint
- add a metadata update endpoint

## Scope Boundaries

- in scope: admin media endpoints, requests, resources, metadata update flow, batch upload flow, route definitions, feature tests, task tracking
- out of scope: media library UI, public media APIs, signed URL issuance, derivative generation, and usage-tracking enforcement

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
- `docs/OPENAPI_SPEC.md`

## Repository Files Inspected

- `app/Models/Media.php`
- `app/Modules/Media/Services/UploadMediaService.php`
- `app/Modules/Media/Services/ReadMediaService.php`
- `app/Modules/Media/Services/DeleteMediaService.php`
- `app/Modules/Media/Repositories/MediaRepository.php`
- `app/Modules/Media/Repositories/EloquentMediaRepository.php`
- `routes/api.php`

## Plan

1. Reuse the existing media storage abstractions and expose them through admin-only HTTP endpoints.
2. Add multipart upload requests, batch upload handling, media resources, and metadata update DTO/service support.
3. Wire protected admin media routes for list, upload, batch upload, show, update, and delete.
4. Add feature tests for upload, batch upload, metadata editing, auth protection, and validation, then validate the full suite and quality checks.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Modules/Media/Data/UpdateMediaMetadataData.php`
- `app/Modules/Media/Repositories/MediaRepository.php`
- `app/Modules/Media/Repositories/EloquentMediaRepository.php`
- `app/Modules/Media/Services/BatchUploadMediaService.php`
- `app/Modules/Media/Services/ListAdminMediaService.php`
- `app/Modules/Media/Services/UpdateMediaMetadataService.php`
- `app/Http/Requests/Api/V1/Admin/StoreMediaRequest.php`
- `app/Http/Requests/Api/V1/Admin/BatchStoreMediaRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdateMediaRequest.php`
- `app/Http/Resources/Api/V1/MediaResource.php`
- `app/Http/Controllers/Api/V1/Admin/MediaController.php`
- `routes/api.php`
- `tests/Feature/MediaApiTest.php`

## Validation

- `php artisan test tests/Feature/MediaApiTest.php`
- `php artisan route:list --path=media`
- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

## Risks Or Follow-Ups

- Media URLs are still derived from the configured media disk, and batch uploads currently apply shared non-file metadata rather than per-file metadata payloads.

## Completion Notes

- Summary: Added admin-only media endpoints for single upload, batch upload, metadata editing, and minimal list/show/delete support, all routed through the existing media abstraction layer.
- Changed files: Added media metadata update DTO support, extended the media repository for listing and metadata updates, added batch/list/update media services, added multipart media requests and a `MediaResource`, introduced an admin `MediaController`, updated `routes/api.php` with protected admin media routes, and added feature coverage in `tests/Feature/MediaApiTest.php`.
- Validation run: `php artisan test tests/Feature/MediaApiTest.php`, `php artisan route:list --path=media`, `php artisan test`, `./vendor/bin/pint --test`, and `./vendor/bin/phpstan analyse` all passed.
- Risks: Batch upload currently applies shared source metadata to all files in a batch, and future usage checks may need to constrain deletion beyond the current soft-archive flow.
- Follow-ups: Add public media metadata endpoints only if later tasks require them, extend metadata editing or per-file batch metadata if editorial workflows need it, and enforce usage-based delete blocking once posts/templates/SEO references exist.
