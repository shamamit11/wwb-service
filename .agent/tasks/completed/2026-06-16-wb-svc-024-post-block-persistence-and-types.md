# Current Task

## Task Summary

Implement post block persistence and allowed block type definitions.

## Requested Outcome

- add post blocks migration
- add post block model
- add block type enum or value object

## Scope Boundaries

- in scope: post-block schema, `PostBlock` model, shared block type definitions, model relationships, task tracking, and tests
- out of scope: post-block repositories or APIs, rendering services, publish workflows, sibling repositories, and editor UI behavior

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/skills/database.md`
- `.agent/skills/template-engine.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `docs/DATABASE_DESIGN.md`
- `docs/TEMPLATE_ENGINE.md`
- `docs/OPENAPI_SPEC.md`

## Repository Files Inspected

- `app/Models/Post.php`
- `app/Models/TemplateBlock.php`
- `app/Http/Requests/Api/V1/Admin/StoreTemplateRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdateTemplateRequest.php`
- `app/Modules/Templates/Services/TemplatePayloadFactory.php`

## Plan

1. Centralize the supported content block types into one shared enum or value object.
2. Add the `post_blocks` schema with deterministic ordering, content fields, and optional source-template traceability.
3. Add the `PostBlock` model and wire inverse relationships from `Post` and `TemplateBlock`.
4. Add focused schema/model tests and validate with migrations, the full test suite, Pint, and Larastan.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Enums/ContentBlockType.php`
- `app/Http/Requests/Api/V1/Admin/StoreTemplateRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdateTemplateRequest.php`
- `app/Models/Post.php`
- `app/Models/PostBlock.php`
- `app/Models/TemplateBlock.php`
- `app/Modules/Templates/Services/TemplatePayloadFactory.php`
- `database/migrations/2026_06_16_191000_create_post_blocks_table.php`
- `tests/Feature/MediaUsageApiTest.php`
- `tests/Feature/PostBlockModelTest.php`

## Validation

- `php artisan migrate`
- `php artisan test tests/Feature/PostBlockModelTest.php`
- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

## Risks Or Follow-Ups

- The persistence layer is in place, but block content validation and post-block repository/services still need to be added when post authoring APIs land.

## Completion Notes

- Summary: Added a shared content block type enum, the `post_blocks` schema, the `PostBlock` model, and ordered post/template block relationships aligned to the template engine.
- Changed files: Added `ContentBlockType`, `PostBlock`, the `post_blocks` migration, updated `Post` and `TemplateBlock` relationships, and switched template request/payload code to the shared block-type definition.
- Validation run: `php artisan migrate`, `php artisan test tests/Feature/PostBlockModelTest.php`, `php artisan test`, `./vendor/bin/pint --test`, and `./vendor/bin/phpstan analyse` all passed.
- Risks: Block content is still persisted as generic markdown/cache/settings fields without higher-level shape validation per block type.
- Follow-ups: Add post-block repositories or services when post creation APIs arrive, and layer block-type-specific content validation on top of the shared `ContentBlockType` enum.
