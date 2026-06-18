# Task Summary

Implement `WB-SVC-046 — Implement Topic Queue schema, repository, and APIs`.

## Requested Outcome

- add `content_topics` persistence and model
- add repository and services for topic queue behavior
- add admin CRUD and status transition APIs
- support duplicate-topic detection and approved-only downstream readiness

## Scope Boundaries

- in scope: migration, model, repository, services, admin endpoints, requests, resources, and tests for content topics
- out of scope: content brief generation itself, topic discovery agent automation, or admin frontend changes

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/knowledge-base/product.md`
- `.agent/skills/ai-content-engine.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/database.md`
- `.agent/TESTING.md`
- `.agent/COMMANDS.md`
- `.agent/TASK-WORKFLOW.md`
- `docs/AI_CONTENT_ENGINE.md`
- `docs/OPENAPI_SPEC.md`
- `docs/TASKS.md`
- `docs/CONTENT_STRATEGY.md`
- `WB_SERVICE_AI_AGENTS_TASKS.md`

## Repository Files Inspected

- `routes/api.php`
- `app/Providers/AppServiceProvider.php`
- `bootstrap/app.php`
- `app/Modules/Categories/Services/CategorySlugResolver.php`
- `app/Modules/Posts/Services/PostSlugResolver.php`
- `app/Modules/Categories/Services/CreateCategoryService.php`
- `app/Modules/Categories/Services/UpdateCategoryService.php`
- `app/Modules/Posts/Services/PublishPostService.php`
- `app/Modules/Posts/Data/PostStateTransitionData.php`
- `app/Modules/Posts/Exceptions/InvalidPostStateTransitionException.php`
- `app/Http/Controllers/Api/V1/Admin/CategoryController.php`
- `app/Http/Controllers/Api/V1/Admin/AiPromptTemplateController.php`
- `app/Http/Requests/Api/V1/Admin/StoreCategoryRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdateCategoryRequest.php`
- `app/Http/Requests/Api/V1/Admin/ListAiPromptTemplatesRequest.php`
- `app/Http/Resources/Api/V1/AiPromptTemplateResource.php`
- `app/Support/AuditActivityLogger.php`
- `tests/Feature/CategoryApiTest.php`
- `tests/Feature/AiPromptTemplateApiTest.php`
- `tests/Feature/AiJobApiTest.php`
- `docs/OPENAPI_SPEC.md`
- `docs/TASKS.md`
- `docs/CONTENT_STRATEGY.md`

## Plan

1. Add `content_topics` schema, model, statuses, and cluster constraints.
2. Implement repository, slug resolver, duplicate detection, and lifecycle services.
3. Add admin CRUD plus approve/reject/mark-used endpoints with explicit transition rules.
4. Add feature tests for filters, duplicate checks, and transitions.
5. Run targeted validation, `php artisan migrate`, then broader tests and record any existing blockers.

## Changed Files

- `.agent/tasks/completed/2026-06-18-wb-svc-046-implement-topic-queue-schema-repository-and-apis.md`
- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Admin/ContentTopicController.php`
- `app/Http/Requests/Api/V1/Admin/ListContentTopicsRequest.php`
- `app/Http/Requests/Api/V1/Admin/StoreContentTopicRequest.php`
- `app/Http/Requests/Api/V1/Admin/TransitionContentTopicRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdateContentTopicRequest.php`
- `app/Http/Resources/Api/V1/ContentTopicResource.php`
- `app/Models/ContentTopic.php`
- `app/Modules/ContentTopics/Data/ContentTopicFiltersData.php`
- `app/Modules/ContentTopics/Data/ContentTopicStateTransitionData.php`
- `app/Modules/ContentTopics/Data/CreateContentTopicData.php`
- `app/Modules/ContentTopics/Data/UpdateContentTopicData.php`
- `app/Modules/ContentTopics/Exceptions/DuplicateContentTopicException.php`
- `app/Modules/ContentTopics/Exceptions/InvalidContentTopicStateTransitionException.php`
- `app/Modules/ContentTopics/Repositories/ContentTopicRepository.php`
- `app/Modules/ContentTopics/Repositories/EloquentContentTopicRepository.php`
- `app/Modules/ContentTopics/Services/ApproveContentTopicService.php`
- `app/Modules/ContentTopics/Services/ContentTopicSlugResolver.php`
- `app/Modules/ContentTopics/Services/CreateContentTopicService.php`
- `app/Modules/ContentTopics/Services/DeleteContentTopicService.php`
- `app/Modules/ContentTopics/Services/ListAdminContentTopicsService.php`
- `app/Modules/ContentTopics/Services/MarkContentTopicUsedService.php`
- `app/Modules/ContentTopics/Services/ReadContentTopicService.php`
- `app/Modules/ContentTopics/Services/RejectContentTopicService.php`
- `app/Modules/ContentTopics/Services/UpdateContentTopicService.php`
- `app/Providers/AppServiceProvider.php`
- `bootstrap/app.php`
- `database/migrations/2026_06_18_130000_create_content_topics_table.php`
- `routes/api.php`
- `tests/Feature/ContentTopicApiTest.php`

## Validation

- `php artisan test tests/Feature/ContentTopicApiTest.php` ✅
- `php artisan migrate` ✅
- `php artisan route:list --path=content-topics` ✅
- `vendor/bin/pint app/Models/ContentTopic.php app/Modules/ContentTopics app/Http/Controllers/Api/V1/Admin/ContentTopicController.php app/Http/Requests/Api/V1/Admin/ListContentTopicsRequest.php app/Http/Requests/Api/V1/Admin/StoreContentTopicRequest.php app/Http/Requests/Api/V1/Admin/TransitionContentTopicRequest.php app/Http/Requests/Api/V1/Admin/UpdateContentTopicRequest.php app/Http/Resources/Api/V1/ContentTopicResource.php tests/Feature/ContentTopicApiTest.php app/Providers/AppServiceProvider.php bootstrap/app.php routes/api.php` ✅
- `php artisan test` ❌ existing unrelated failure in `tests/Feature/ActivityLogTest.php::test_editorial_mutations_are_recorded_with_curated_audit_payloads`

## Risks Or Follow-Ups

- later content-brief generation work still needs to call approved-topic gating explicitly when brief creation is implemented
- duplicate detection is intentionally conservative on `(cluster + title)` and `(cluster + primary_keyword)` and may need widening if editorial similarity rules become more advanced

## Completion Notes

- Summary: added the real topic queue backend with persistence, duplicate checks, explicit lifecycle services, admin CRUD/read APIs, and approve/reject/mark-used transitions.
- Changed files: content topic schema/model, repository/service layer, admin controller/requests/resources, route and exception wiring, and the feature test listed above.
- Validation run: topic API tests passed, migration succeeded, routes are registered, formatting passed, and the full suite still fails only on the existing AWS credential lookup in `ActivityLogTest`.
- Risks: transition enforcement currently lives in the topic services, so any future brief-generation workflow must reuse those services instead of bypassing them with direct model writes.
- Follow-ups: wire the upcoming brief-generation pipeline to `ContentTopic::canGenerateContentBrief()` or the topic transition services so only approved topics enter downstream AI generation.
