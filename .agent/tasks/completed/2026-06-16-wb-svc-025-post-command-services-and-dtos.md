# Current Task

## Task Summary

Implement service-layer use cases for creating, updating, and deleting posts and blocks.

## Requested Outcome

- add create/update/delete services
- add DTOs
- add transaction handling

## Scope Boundaries

- in scope: post command DTOs, create/update/delete services, block payload mapping, transaction handling, task tracking, and tests
- out of scope: post controllers and requests, publish/schedule services, public queries, sibling repositories, and editor UI behavior

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/ARCHITECTURE.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/database.md`
- `.agent/skills/template-engine.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `docs/DATABASE_DESIGN.md`
- `docs/OPENAPI_SPEC.md`

## Repository Files Inspected

- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Models/Post.php`
- `app/Models/PostBlock.php`
- `app/Models/TemplateBlock.php`
- `app/Modules/Templates/Services/TemplatePayloadFactory.php`
- `app/Modules/Categories/Services/CreateCategoryService.php`
- `app/Modules/Templates/Services/CreateTemplateService.php`

## Plan

1. Add command-level DTOs for post mutations and block payloads, separate from the persistence DTOs already used by the repository.
2. Add helpers for slug generation and consistent block payload normalization into `post_blocks` persistence fields.
3. Add create, update, and delete services that orchestrate post, tags, and blocks inside one transaction.
4. Add focused service tests and validate with the Laravel test suite plus quality checks.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Modules/Posts/Data/CreatePostBlockData.php`
- `app/Modules/Posts/Data/CreatePostCommandData.php`
- `app/Modules/Posts/Data/PostBlockPayloadData.php`
- `app/Modules/Posts/Data/UpdatePostCommandData.php`
- `app/Modules/Posts/Exceptions/InvalidPostBlockPayloadException.php`
- `app/Modules/Posts/Repositories/EloquentPostBlockRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Repositories/PostBlockRepository.php`
- `app/Modules/Posts/Services/CreatePostService.php`
- `app/Modules/Posts/Services/DeletePostService.php`
- `app/Modules/Posts/Services/PostBlockPayloadMapper.php`
- `app/Modules/Posts/Services/PostBlockPayloadValidator.php`
- `app/Modules/Posts/Services/PostSlugResolver.php`
- `app/Modules/Posts/Services/UpdatePostService.php`
- `app/Providers/AppServiceProvider.php`
- `tests/Feature/PostCommandServiceTest.php`

## Validation

- `php artisan test tests/Feature/PostCommandServiceTest.php`
- `php artisan test tests/Feature/PostRepositoryTest.php`
- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

## Risks Or Follow-Ups

- Block payload mapping will initially normalize into the current markdown/cache/settings storage model; richer type-specific validation can be layered on top later.

## Completion Notes

- Summary: Added command-layer DTOs and create/update/delete services for posts, with shared block payload validation, normalization, and transactional persistence across posts, tags, and blocks.
- Changed files: Added post command DTOs, a block payload DTO and normalized block persistence DTO, a block payload exception, a post-block repository, a slug resolver, block validation and mapping services, create/update/delete post services, an app-container binding for the post-block repository, and focused feature coverage for service behavior.
- Validation run: `php artisan test tests/Feature/PostCommandServiceTest.php`, `php artisan test tests/Feature/PostRepositoryTest.php`, `php artisan test`, `./vendor/bin/pint --test`, and `./vendor/bin/phpstan analyse` all passed.
- Risks: Block payload validation currently enforces minimal structural correctness and normalization into markdown/settings/plain-text caches, but richer semantic validation per block type may still be needed when admin APIs are exposed.
- Follow-ups: Build admin post controllers/requests/resources on top of these services, and align template seeding and future AI draft generation to the `CreatePostCommandData` and `PostBlockPayloadData` shapes rather than bypassing them.
