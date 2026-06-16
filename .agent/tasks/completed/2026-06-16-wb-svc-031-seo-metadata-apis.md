# Current Task

## Task Summary

Implement admin SEO metadata APIs and resource serializers on top of the SEO persistence layer.

## Requested Outcome

- add SEO controllers
- add SEO requests
- add SEO resources

## Scope Boundaries

- in scope: read/write SEO endpoints, route wiring, request validation, seoable target resolution, services, and API tests
- out of scope: scoring endpoint, frontend SEO rendering, AI SEO generation, and sibling repositories

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
- `.agent/skills/laravel-api.md`
- `.agent/skills/api-contracts.md`
- `.agent/skills/seo.md`
- `docs/OPENAPI_SPEC.md`

## Repository Files Inspected

- `app/Models/SeoMetadata.php`
- `app/Models/Post.php`
- `app/Models/Category.php`
- `app/Models/KnowledgeBaseEntry.php`
- `app/Modules/Seo/Repositories/SeoMetadataRepository.php`
- `app/Modules/Seo/Repositories/EloquentSeoMetadataRepository.php`
- `routes/api.php`

## Plan

1. Add seoable target resolution and small read/upsert services over the existing SEO repository.
2. Add admin SEO request/resource/controller classes and route wiring for `GET` and `PUT`.
3. Add feature coverage for auth, read/write behavior on posts and categories, and validation for canonical, robots, OG, and schema fields.
4. Validate with targeted tests, Pint, and the full suite, then archive the task note.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Admin/SeoMetadataController.php`
- `app/Http/Requests/Api/V1/Admin/UpdateSeoMetadataRequest.php`
- `app/Http/Resources/Api/V1/SeoMetadataResource.php`
- `app/Modules/Seo/Services/ReadSeoMetadataService.php`
- `app/Modules/Seo/Services/ResolveSeoableTargetService.php`
- `app/Modules/Seo/Services/UpsertSeoMetadataService.php`
- `routes/api.php`
- `tests/Feature/SeoMetadataApiTest.php`

## Validation

- `php artisan test tests/Feature/SeoMetadataApiTest.php`
- `php artisan test tests/Feature/SeoMetadataRepositoryTest.php`
- `vendor/bin/pint --test`
- `php artisan test`

## Risks Or Follow-Ups

- The scoring endpoint is intentionally deferred; this task covers only metadata read/write.

## Completion Notes

- Summary: Added admin SEO read/write endpoints, request validation, target-resolution services, and a resource serializer that supports canonical, robots, Open Graph, schema, and keyword fields.
- Validation run: targeted SEO API and repository tests, Pint, and full `php artisan test` all passed.
- Risks: the scoring endpoint remains intentionally deferred; this task covers only metadata management.
