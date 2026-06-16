# Current Task

## Task Summary

Centralize deterministic slug generation and uniqueness behavior behind a shared service.

## Requested Outcome

- add shared slug service
- keep unique suffix strategy consistent

## Scope Boundaries

- in scope: shared slug generation service, resolver consolidation for categories/tags/templates/posts/knowledge entries, and slug-focused tests
- out of scope: model hooks, route changes, sibling repositories, and unrelated content-service refactors

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/TESTING.md`

## Repository Files Inspected

- `app/Modules/Categories/Services/CategorySlugResolver.php`
- `app/Modules/Tags/Services/TagSlugResolver.php`
- `app/Modules/Templates/Services/TemplateSlugResolver.php`
- `app/Modules/Posts/Services/PostSlugResolver.php`
- `app/Modules/KnowledgeBase/Services/KnowledgeBaseEntrySlugResolver.php`
- slug-related feature tests under `tests/Feature`

## Plan

1. Add a shared slug-generation service that owns normalization, fallback slug creation, and numeric suffixing.
2. Refactor each domain-specific resolver into a thin wrapper around that shared service so service-layer call sites stay explicit.
3. Add focused test coverage for deterministic fallback and uniqueness behavior, then validate with targeted and full tests.
4. Archive the completed task note and reset `current-task.md`.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Modules/Categories/Services/CategorySlugResolver.php`
- `app/Modules/KnowledgeBase/Services/KnowledgeBaseEntrySlugResolver.php`
- `app/Modules/Posts/Services/PostSlugResolver.php`
- `app/Modules/Tags/Services/TagSlugResolver.php`
- `app/Modules/Templates/Services/TemplateSlugResolver.php`
- `app/Support/SlugGenerator.php`
- `tests/Feature/SlugGeneratorTest.php`

## Validation

- `php artisan test tests/Feature/SlugGeneratorTest.php tests/Feature/CategoryApiTest.php tests/Feature/TagApiTest.php tests/Feature/TemplateApiTest.php tests/Feature/KnowledgeBaseApiTest.php tests/Feature/PostCommandServiceTest.php`
- `vendor/bin/pint --test`
- `php artisan test`

## Risks Or Follow-Ups

- This task keeps resolver wrappers for clarity; a later cleanup could replace them with a shared contract if that becomes useful.

## Completion Notes

- Summary: Centralized slug normalization, fallback, and uniqueness suffixing in a shared `SlugGenerator` service while keeping explicit per-domain resolver wrappers in place for categories, tags, templates, posts, and knowledge-base entries.
- Validation run: targeted slug and feature slices, Pint, and full `php artisan test` all passed.
- Risks: resolver wrappers remain intentionally thin; if more slugged domains are added later, reuse the shared service rather than copying algorithm logic again.
