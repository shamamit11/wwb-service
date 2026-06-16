# Current Task

## Task Summary

Implement the template and template-block persistence layer with ordered block retrieval.

## Requested Outcome

- add template migrations
- add models
- add repositories

## Scope Boundaries

- in scope: template and template-block schema, Eloquent models, repository contracts and implementations, ordered block retrieval, tests, task tracking
- out of scope: template API endpoints, preview rendering, seed-post flows, sibling repositories, and visual template editing

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
- `.agent/skills/template-engine.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `docs/TEMPLATE_ENGINE.md`
- `docs/DATABASE_DESIGN.md`
- `docs/OPENAPI_SPEC.md`

## Repository Files Inspected

- `app/Providers/AppServiceProvider.php`
- `app/Models/Category.php`
- `app/Models/Tag.php`
- `app/Modules/Categories/Repositories/CategoryRepository.php`
- `app/Modules/Tags/Repositories/TagRepository.php`

## Plan

1. Align the template and template-block schema to the database design and template-engine docs.
2. Add `Template` and `TemplateBlock` models with ordered block relationships and the documented casts/keys.
3. Add repository contracts and implementations for template CRUD plus ordered block retrieval and replacement.
4. Add focused schema/repository tests and validate with migrations, the test suite, Pint, and Larastan.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Models/Template.php`
- `app/Models/TemplateBlock.php`
- `app/Modules/Templates/Data/CreateTemplateBlockData.php`
- `app/Modules/Templates/Data/CreateTemplateData.php`
- `app/Modules/Templates/Data/UpdateTemplateData.php`
- `app/Modules/Templates/Repositories/EloquentTemplateRepository.php`
- `app/Modules/Templates/Repositories/TemplateRepository.php`
- `app/Providers/AppServiceProvider.php`
- `database/migrations/2026_06_16_181000_create_templates_table.php`
- `database/migrations/2026_06_16_181100_create_template_blocks_table.php`
- `tests/Feature/TemplateRepositoryTest.php`

## Validation

- `php artisan migrate`
- `php artisan test tests/Feature/TemplateRepositoryTest.php`
- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

## Risks Or Follow-Ups

- This task establishes persistence only; preview rendering, block compatibility validation, and post-seeding logic still need higher-level services later.

## Completion Notes

- Summary: Added the template and template-block persistence layer with documented schema, ordered block relationships, and repository support for create, update, lookup, ordered retrieval, and delete flows.
- Changed files: Added `Template` and `TemplateBlock` models, template create/update DTOs, a repository contract and Eloquent implementation, template/table migrations, service-container binding, and focused repository tests.
- Validation run: `php artisan migrate`, `php artisan test tests/Feature/TemplateRepositoryTest.php`, `php artisan test`, `./vendor/bin/pint --test`, and `./vendor/bin/phpstan analyse` all passed.
- Risks: Block replacement currently uses full delete-and-recreate semantics on update, which is acceptable for the persistence baseline but will need revision once template-block identity or history must be preserved across edits.
- Follow-ups: Add higher-level services for block compatibility validation, post seeding from templates, and any future APIs that need to expose or mutate templates.
