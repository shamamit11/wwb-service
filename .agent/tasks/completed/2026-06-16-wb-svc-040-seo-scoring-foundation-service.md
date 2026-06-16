# Current Task

## Task Summary

Implement an advisory SEO scoring foundation for posts.

## Requested Outcome

- add a scoring service
- add a structured scoring breakdown
- add endpoint or internal API support
- ensure a post can receive a structured SEO score with subscores

## Scope Boundaries

- in scope: post-focused advisory scoring, reusable subscores, internal endpoint, and focused tests
- out of scope: publish gating, frontend score visualization, and category/knowledge-base scoring

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

- `app/Http/Controllers/Api/V1/Admin/SchemaController.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Http/Resources/Api/V1/SeoMetadataResource.php`
- `app/Models/SeoMetadata.php`
- `app/Modules/Seo/Services/FindRelatedContentService.php`
- `app/Modules/Seo/Services/GenerateSchemaPayloadService.php`
- `app/Modules/Seo/Services/SuggestInternalLinksService.php`
- `routes/api.php`
- `tests/Feature/InternalLinkingServiceTest.php`
- `tests/Feature/SchemaDataApiTest.php`

## Plan

1. Add scoring calculators and a post SEO scoring service that reuses existing metadata, schema, and internal-linking signals.
2. Expose the result through a thin internal admin endpoint and resource serializer.
3. Add focused feature coverage, then validate with targeted tests, Pint, and the full suite.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Admin/SeoScoreController.php`
- `app/Http/Resources/Api/V1/SeoScoreResource.php`
- `app/Modules/Seo/Scoring/ContentScoreCalculator.php`
- `app/Modules/Seo/Scoring/InternalLinkingScoreCalculator.php`
- `app/Modules/Seo/Scoring/MetadataScoreCalculator.php`
- `app/Modules/Seo/Scoring/SchemaScoreCalculator.php`
- `app/Modules/Seo/Services/ScorePostSeoService.php`
- `routes/api.php`
- `tests/Feature/SeoScoringApiTest.php`

## Validation

- `php artisan test tests/Feature/SeoScoringApiTest.php tests/Feature/SchemaDataApiTest.php tests/Feature/InternalLinkingServiceTest.php` — passed
- `vendor/bin/pint --test` — passed
- `php artisan test` — passed

## Risks Or Follow-Ups

- This foundation is heuristic and advisory; if editorial teams want calibrated scoring later, the rubric weights should be externalized rather than hard-coded into the first pass.

## Completion Notes

- Added advisory SEO scoring calculators for metadata, content quality, schema coverage, and internal linking under `app/Modules/Seo/Scoring/`.
- Added `ScorePostSeoService` to aggregate subscores into a structured 100-point score with grade and recommendations for posts.
- Added an authenticated internal endpoint at `GET /api/v1/admin/seo/score/{seoableType}/{seoableId}` backed by `SeoScoreResource`.
- Added feature coverage for auth, strong-score payloads with subscores, and low-score recommendations for sparse posts.
