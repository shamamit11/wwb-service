# Current Task

## Task Summary

Implement the SEO metadata persistence layer with polymorphic one-to-one support across content entities.

## Requested Outcome

- add SEO metadata migration
- add `SeoMetadata` model
- add SEO repository layer

## Scope Boundaries

- in scope: schema, model/constants/casts/relations, polymorphic repository support, container binding, and repository tests
- out of scope: SEO API endpoints, scoring/generation services, sibling repositories, and frontend-facing SEO rendering

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/skills/database.md`
- `.agent/skills/seo.md`
- `docs/DATABASE_DESIGN.md`
- `docs/SEO_STRATEGY.md`

## Repository Files Inspected

- `app/Models/Post.php`
- `app/Models/Category.php`
- `app/Models/KnowledgeBaseEntry.php`
- `app/Providers/AppServiceProvider.php`
- `tests/Feature/PostRepositoryTest.php`
- `tests/Feature/KnowledgeBaseEntryRepositoryTest.php`
- `tests/Feature/MediaUsageApiTest.php`

## Plan

1. Add the `seo_metadata` table with polymorphic owner columns, media reference, boolean flags, and indexes from the documented schema.
2. Add the `SeoMetadata` model plus morph-one relations on posts, categories, and knowledge-base entries.
3. Add DTOs and an SEO repository that can create, update, find, and delete metadata for a `seoable` entity while preserving the one-to-one contract.
4. Add repository coverage for schema baseline and polymorphic storage on posts/categories, then validate with migrations and the test suite.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Models/Category.php`
- `app/Models/KnowledgeBaseEntry.php`
- `app/Models/Post.php`
- `app/Models/SeoMetadata.php`
- `app/Modules/Seo/Data/CreateSeoMetadataData.php`
- `app/Modules/Seo/Data/UpdateSeoMetadataData.php`
- `app/Modules/Seo/Repositories/EloquentSeoMetadataRepository.php`
- `app/Modules/Seo/Repositories/SeoMetadataRepository.php`
- `app/Providers/AppServiceProvider.php`
- `database/migrations/2026_06_16_193000_create_seo_metadata_table.php`
- `tests/Feature/MediaUsageApiTest.php`
- `tests/Feature/SeoMetadataRepositoryTest.php`

## Validation

- `php artisan test tests/Feature/SeoMetadataRepositoryTest.php`
- `php artisan migrate`
- `vendor/bin/pint --test`
- `php artisan test tests/Feature/MediaUsageApiTest.php tests/Feature/SeoMetadataRepositoryTest.php`
- `php artisan test`

## Risks Or Follow-Ups

- This task will wire the relation for knowledge-base entries too for extensibility, but acceptance will be validated explicitly on posts and categories.

## Completion Notes

- Summary: Added the `seo_metadata` polymorphic table, `SeoMetadata` model, owner-centric SEO repository layer, and morph-one relations on posts, categories, and knowledge-base entries.
- Validation run: targeted SEO repository test, migration, Pint, targeted media-usage plus SEO tests, and full `php artisan test` all passed.
- Risks: this task wires extensibility for knowledge-base entries too, but SEO API endpoints and higher-level services remain separate follow-up work.
