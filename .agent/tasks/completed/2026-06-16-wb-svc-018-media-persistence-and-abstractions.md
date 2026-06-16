# Current Task

## Task Summary

Implement media persistence and core storage service abstractions.

## Requested Outcome

- add the media migration
- add the media model
- add service contracts and implementations for upload, read, and delete flows

## Scope Boundaries

- in scope: media schema, model, repository, storage abstractions, upload/read/delete service contracts, tests, task tracking
- out of scope: media API endpoints, sibling repositories, image derivative generation, signed URL issuance, and AI job orchestration

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
- `.agent/skills/media-service.md`
- `docs/MEDIA_SERVICE.md`
- `docs/DATABASE_DESIGN.md`
- `docs/OPENAPI_SPEC.md`

## Repository Files Inspected

- `config/filesystems.php`
- `.env.example`
- `app/Providers/AppServiceProvider.php`
- `app/Modules/README.md`

## Plan

1. Read the media storage and schema specs, then reconcile them with the current repository state where `ai_jobs` does not yet exist.
2. Add the `media` migration and `Media` model, keeping metadata in MySQL and object truth in the configured media disk.
3. Add repository plus upload/read/delete service contracts and concrete storage-backed implementations.
4. Add focused tests for schema, upload persistence, read behavior, and deletion flow, then validate the full suite and quality checks.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Models/Media.php`
- `app/Modules/Media/Data/CreateMediaData.php`
- `app/Modules/Media/Data/UploadMediaData.php`
- `app/Modules/Media/Repositories/MediaRepository.php`
- `app/Modules/Media/Repositories/EloquentMediaRepository.php`
- `app/Modules/Media/Services/Contracts/MediaStorage.php`
- `app/Modules/Media/Services/Contracts/MediaUploader.php`
- `app/Modules/Media/Services/Contracts/MediaReader.php`
- `app/Modules/Media/Services/Contracts/MediaDeleter.php`
- `app/Modules/Media/Services/FilesystemMediaStorage.php`
- `app/Modules/Media/Services/UploadMediaService.php`
- `app/Modules/Media/Services/ReadMediaService.php`
- `app/Modules/Media/Services/DeleteMediaService.php`
- `app/Providers/AppServiceProvider.php`
- `database/migrations/2026_06_16_173000_create_media_table.php`
- `tests/Feature/MediaServiceTest.php`

## Validation

- `php artisan migrate`
- `php artisan test tests/Feature/MediaServiceTest.php`
- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

## Risks Or Follow-Ups

- The `generated_by_ai_job_id` foreign key is deferred until the `ai_jobs` table exists, and URL generation currently assumes the configured media disk exposes stable public URLs.

## Completion Notes

- Summary: Added the `media` table, `Media` model, a repository layer, and storage-backed upload/read/delete abstractions aligned to the configured media disk and R2-oriented metadata model.
- Changed files: Added media migration and model, media DTOs and repository classes under `app/Modules/Media`, service contracts plus concrete filesystem-backed implementations for upload/read/delete, registered bindings in `AppServiceProvider`, and added focused coverage in `tests/Feature/MediaServiceTest.php`.
- Validation run: `php artisan migrate`, `php artisan test tests/Feature/MediaServiceTest.php`, `php artisan test`, `./vendor/bin/pint --test`, and `./vendor/bin/phpstan analyse` all passed.
- Risks: The schema includes `source_type`, `source_url`, and `attribution_text` to match the media spec and OpenAPI contract even though the abbreviated database table snippet omits them, and the `generated_by_ai_job_id` foreign key is intentionally deferred until `ai_jobs` exists.
- Follow-ups: Add the `generated_by_ai_job_id` foreign key later, introduce media API endpoints, and extend the service layer for usage checks or derivative generation when those tasks arrive.
