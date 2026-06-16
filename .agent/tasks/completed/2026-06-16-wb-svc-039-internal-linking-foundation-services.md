# Current Task

## Task Summary

Implement baseline related-content discovery and internal link suggestion services.

## Requested Outcome

- add a related-content query service
- add an internal link suggestion service baseline
- ensure the service can return candidate related content for a post or draft context

## Scope Boundaries

- in scope: service-side scoring over published posts and active knowledge-base entries, draft-context DTOs, and focused tests
- out of scope: editor UI integration, persisted link graphs, automatic in-body insertion, and public API endpoints

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/seo.md`
- `.agent/skills/seo.md`
- `.agent/skills/laravel-api.md`

## Repository Files Inspected

- `app/Http/Resources/Api/V1/KnowledgeBaseEntryResource.php`
- `app/Models/KnowledgeBaseEntry.php`
- `app/Models/Post.php`
- `app/Models/Tag.php`
- `app/Modules/KnowledgeBase/Repositories/EloquentKnowledgeBaseEntryRepository.php`
- `app/Modules/KnowledgeBase/Repositories/KnowledgeBaseEntryRepository.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Services/UpdatePostService.php`
- `app/Modules/Seo/Schema/ArticleSchemaBuilder.php`
- `tests/Feature/KnowledgeBaseApiTest.php`
- `tests/Feature/PostRepositoryTest.php`

## Plan

1. Add DTOs and a related-content query service that scores published posts and active knowledge-base entries from existing metadata signals.
2. Add a suggestion service that reuses the query service for post and draft contexts.
3. Add focused feature tests, then validate with targeted tests, Pint, and the full suite.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Modules/Seo/Data/InternalLinkContextData.php`
- `app/Modules/Seo/Data/InternalLinkSuggestionData.php`
- `app/Modules/Seo/Data/RelatedContentCandidateData.php`
- `app/Modules/Seo/Services/FindRelatedContentService.php`
- `app/Modules/Seo/Services/SuggestInternalLinksService.php`
- `tests/Feature/InternalLinkingServiceTest.php`

## Validation

- `php artisan test tests/Feature/InternalLinkingServiceTest.php tests/Feature/PostRepositoryTest.php tests/Feature/KnowledgeBaseApiTest.php` — passed
- `vendor/bin/pint --test` — passed
- `php artisan test` — passed

## Risks Or Follow-Ups

- This baseline will rank from current relational and text signals only; if the product later needs semantic similarity or link analytics, a dedicated indexing layer should sit behind these services rather than replacing them.

## Completion Notes

- Added `FindRelatedContentService` to score published posts and active knowledge-base entries from existing category, tag, focus-keyword, text-overlap, and linked-post signals.
- Added `SuggestInternalLinksService` plus draft/post context DTOs to produce baseline suggestion outputs for editor workflows without adding UI coupling.
- Added focused feature coverage for post-context related content, draft-context suggestions, filtering to public published posts and active knowledge-base entries, and canonical URL exposure for candidates.
