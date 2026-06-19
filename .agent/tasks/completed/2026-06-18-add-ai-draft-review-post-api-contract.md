# Task Summary

Add explicit AI draft-review contract support to the admin posts API.

## Requested Outcome

- expose AI provenance fields directly on post API resources
- add admin post list filters for AI-generated draft review use cases
- keep the contract aligned with the data actually persisted in `posts.meta`

## Scope Boundaries

- in scope: request validation, DTO filters, repository search filters, post resource fields, docs-safe contract updates, and tests
- out of scope: admin UI changes, new persistence schema, or changing AI draft generation payloads

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/knowledge-base/api-standards.md`
- `.agent/skills/laravel-api.md`

## Repository Files Inspected

- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Http/Controllers/Api/V1/Admin/PostController.php`
- `app/Http/Requests/Api/V1/Admin/ListPostsRequest.php`
- `app/Modules/Posts/Data/PostFiltersData.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Services/ListAdminPostsService.php`
- `app/AI/Tools/SavePostDraftTool.php`
- `tests/Feature/PostApiTest.php`
- `tests/Feature/PostRepositoryTest.php`

## Plan

1. Extend the admin post filter DTO/request/repository search logic with explicit AI provenance filters.
2. Expose first-class AI provenance fields on `PostResource` without forcing consumers to parse `meta`.
3. Add feature and repository coverage for the new contract and validate with focused post tests.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Requests/Api/V1/Admin/ListPostsRequest.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Modules/Posts/Data/PostFiltersData.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `tests/Feature/PostApiTest.php`
- `tests/Feature/PostRepositoryTest.php`

## Validation

- `php artisan test tests/Feature/PostApiTest.php`
- `php artisan test tests/Feature/PostRepositoryTest.php`
- `php artisan test`

## Risks Or Follow-Ups

- `generated_by_ai_job_id` is not currently persisted on posts, so the contract should expose only persisted provenance fields plus a derived `is_ai_generated` flag unless the write path is extended later.

## Completion Notes

- Added explicit admin post list filters for `is_ai_generated`, `source_content_brief_id`, `source_content_topic_id`, and `generated_by_ai_job_id`.
- Exposed top-level post resource provenance fields: `is_ai_generated`, `source_content_brief_id`, `source_content_topic_id`, `generated_by_ai_job_id`, and `generated_by`.
- Kept the contract aligned to existing `posts.meta` persistence rather than inventing new schema fields, which resolves the admin draft-review blocker without changing the AI draft write path.
