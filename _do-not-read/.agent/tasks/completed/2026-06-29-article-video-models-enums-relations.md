# Task Summary

Implement `AV-SVC-1.2` for Article Videos by adding models, enums, helper methods, and `Post` relations on top of the new persistence schema.

## Requested Outcome

- create `ArticleVideoRecommendation`, `ArticleVideo`, and `ArticleVideoMemoryEntry` models
- centralize Article Video statuses and related domain values in enums
- add the required `Post` and inverse model relations
- validate casts and relation wiring with focused automated coverage

## Scope Boundaries

- in scope: models, enum definitions, casts, relationship methods, lightweight state helpers, focused tests
- out of scope: repositories, services, AI workflows, API endpoints, Admin UI

## Assumptions

- feature-scoped backed enums are acceptable for this domain because the repo already uses them in newer bounded modules
- Article Video records may remain partially populated while async generation or rendering is in progress, so model casts must tolerate nullable payloads
- a recommendation may have more than one historical video linked over time, so the recommendation-to-video inverse relation should not assume one-to-one

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/skills/database.md`
- `.agent/TESTING.md`
- `.agent/COMMANDS.md`

## Repository Files Inspected

- `app/Models/Post.php`
- `app/Models/ContentTopic.php`
- `app/Models/Media.php`
- `app/Models/Category.php`
- `app/Models/NewsletterCampaign.php`
- `app/Models/NewsletterSubscriber.php`
- `app/Models/AiJob.php`
- `database/factories/UserFactory.php`
- `database/migrations/2026_06_16_164000_create_categories_table.php`
- `tests/Feature/NewsletterFoundationTest.php`

## Plan

1. Add Article Video enums in a feature-scoped namespace.
2. Create the three models with fillable fields, casts, relations, and small state helpers.
3. Extend `Post` with the Article Video relations.
4. Add a focused feature test for casts, enum values, and relationship behavior.
5. Run targeted validation for the new test.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Models/ArticleVideoRecommendation.php`
- `app/Models/ArticleVideo.php`
- `app/Models/ArticleVideoMemoryEntry.php`
- `app/Models/Post.php`
- `app/Modules/ArticleVideos/Enums/ArticleVideoRecommendationPriority.php`
- `app/Modules/ArticleVideos/Enums/ArticleVideoRecommendationFormat.php`
- `app/Modules/ArticleVideos/Enums/ArticleVideoRecommendationStatus.php`
- `app/Modules/ArticleVideos/Enums/ArticleVideoStatus.php`
- `app/Modules/ArticleVideos/Enums/ArticleVideoRenderMode.php`
- `tests/Feature/ArticleVideoFoundationTest.php`

## Validation

- `php -l app/Models/ArticleVideoRecommendation.php && php -l app/Models/ArticleVideo.php && php -l app/Models/ArticleVideoMemoryEntry.php && php -l app/Models/Post.php && php -l tests/Feature/ArticleVideoFoundationTest.php && php -l app/Modules/ArticleVideos/Enums/ArticleVideoRecommendationPriority.php && php -l app/Modules/ArticleVideos/Enums/ArticleVideoRecommendationFormat.php && php -l app/Modules/ArticleVideos/Enums/ArticleVideoRecommendationStatus.php && php -l app/Modules/ArticleVideos/Enums/ArticleVideoStatus.php && php -l app/Modules/ArticleVideos/Enums/ArticleVideoRenderMode.php`
- `DB_CONNECTION=sqlite DB_DATABASE=/Users/amitsharma/Herd/widewebblog/service/database/database.sqlite php artisan test tests/Feature/ArticleVideoFoundationTest.php`
- `vendor/bin/pint app/Models/ArticleVideoRecommendation.php app/Models/ArticleVideo.php app/Models/ArticleVideoMemoryEntry.php app/Models/Post.php app/Modules/ArticleVideos/Enums/ArticleVideoRecommendationPriority.php app/Modules/ArticleVideos/Enums/ArticleVideoRecommendationFormat.php app/Modules/ArticleVideos/Enums/ArticleVideoRecommendationStatus.php app/Modules/ArticleVideos/Enums/ArticleVideoStatus.php app/Modules/ArticleVideos/Enums/ArticleVideoRenderMode.php tests/Feature/ArticleVideoFoundationTest.php`

## Risks Or Follow-Ups

- workflow-level rules such as duplicate active drafts and render eligibility still need enforcement in service logic, not just helper methods
- if the team later wants status filtering conventions shared across layers, a repository or query object should own that instead of expanding model helpers too far

## Completion Notes

- added feature-scoped enums for recommendation priority, recommendation format, recommendation status, video status, and render mode
- created `ArticleVideoRecommendation`, `ArticleVideo`, and `ArticleVideoMemoryEntry` models with enum casts, inverse relations, and lightweight state helpers
- extended `Post` with Article Video recommendation, video, and memory-entry relations
- added focused coverage for enum values, casts, helper behavior, and relationship wiring
