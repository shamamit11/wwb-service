# Task Summary

Implement `WB-SVC-049 — Implement Content Brief schema and APIs`.

## Requested Outcome

- add `content_briefs` persistence and model
- add repository and service support for structured briefs
- add admin APIs for listing, reading, updating, approving, and generating briefs from topics
- link briefs to topics and enforce approved-topic-only brief generation

## Scope Boundaries

- in scope: schema, model, repository/service layer, admin endpoints, requests/resources, deterministic brief generation, and tests
- out of scope: the AI `ContentBriefAgent`, queued brief generation, blog draft generation, admin UI, and sibling apps

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/ai-content-engine.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/database.md`
- `.agent/skills/testing.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `docs/AI_CONTENT_ENGINE.md`
- `WB_SERVICE_AI_AGENTS_TASKS.md`

## Repository Files Inspected

- `routes/api.php`
- `bootstrap/app.php`
- `app/Http/Controllers/Api/V1/Admin/ContentTopicController.php`
- `app/Http/Resources/Api/V1/ContentTopicResource.php`
- `app/Http/Requests/Api/V1/Admin/ListContentTopicsRequest.php`
- `app/Http/Requests/Api/V1/Admin/StoreContentTopicRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdateContentTopicRequest.php`
- `app/Http/Requests/Api/V1/Admin/TransitionContentTopicRequest.php`
- `app/Support/AuditActivityLogger.php`
- `app/AI/DTO/ContentBriefInput.php`
- `app/AI/DTO/ContentBriefResult.php`
- `app/Models/ContentTopic.php`
- `app/Models/KnowledgeBaseEntry.php`
- `app/Modules/ContentTopics/Services/ApproveContentTopicService.php`
- `app/Modules/ContentTopics/Services/UpdateContentTopicService.php`
- `app/Modules/ContentTopics/Services/ListAdminContentTopicsService.php`
- `app/Modules/ContentTopics/Data/ContentTopicFiltersData.php`
- `app/Modules/KnowledgeBase/Repositories/KnowledgeBaseEntryRepository.php`
- `app/Modules/KnowledgeBase/Repositories/EloquentKnowledgeBaseEntryRepository.php`
- `app/Modules/KnowledgeBase/Data/KnowledgeBaseEntryFiltersData.php`
- `app/Modules/Seo/Services/FindRelatedContentService.php`
- `app/Modules/Seo/Services/SuggestInternalLinksService.php`
- `app/Modules/Seo/Data/InternalLinkContextData.php`
- `app/Modules/Seo/Data/RelatedContentCandidateData.php`
- `tests/Feature/ContentTopicApiTest.php`
- `tests/Feature/TopicDiscoveryAgentTest.php`
- `tests/Feature/TopicDiscoveryExecutionTest.php`

## Plan

1. Add the `content_briefs` schema, model, statuses, and topic relationship.
2. Implement repository and service support for list/read/update/approve plus deterministic brief generation from approved topics.
3. Add admin endpoints, requests, resources, and route wiring for content brief review.
4. Add focused feature coverage for approved-topic generation, structured brief output, review updates, and approval.
5. Run migrations and targeted tests, then record broader validation results.

## Changed Files

- `.agent/tasks/completed/2026-06-18-wb-svc-049-implement-content-brief-schema-and-apis.md`
- `.agent/tasks/current-task.md`
- `database/migrations/2026_06_18_160000_create_content_briefs_table.php`
- `app/Models/ContentBrief.php`
- `app/Models/ContentTopic.php`
- `app/Modules/ContentBriefs/Data/ContentBriefFiltersData.php`
- `app/Modules/ContentBriefs/Data/CreateContentBriefData.php`
- `app/Modules/ContentBriefs/Data/UpdateContentBriefData.php`
- `app/Modules/ContentBriefs/Data/GeneratedContentBriefData.php`
- `app/Modules/ContentBriefs/Exceptions/ContentBriefGenerationNotAllowedException.php`
- `app/Modules/ContentBriefs/Exceptions/InvalidContentBriefStateTransitionException.php`
- `app/Modules/ContentBriefs/Repositories/ContentBriefRepository.php`
- `app/Modules/ContentBriefs/Repositories/EloquentContentBriefRepository.php`
- `app/Modules/ContentBriefs/Services/ContentBriefSlugResolver.php`
- `app/Modules/ContentBriefs/Services/ListAdminContentBriefsService.php`
- `app/Modules/ContentBriefs/Services/ReadContentBriefService.php`
- `app/Modules/ContentBriefs/Services/UpdateContentBriefService.php`
- `app/Modules/ContentBriefs/Services/ApproveContentBriefService.php`
- `app/Modules/ContentBriefs/Services/GenerateContentBriefFromTopicService.php`
- `app/Http/Controllers/Api/V1/Admin/ContentBriefController.php`
- `app/Http/Controllers/Api/V1/Admin/ContentTopicController.php`
- `app/Http/Requests/Api/V1/Admin/ListContentBriefsRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdateContentBriefRequest.php`
- `app/Http/Resources/Api/V1/ContentBriefResource.php`
- `app/Providers/AppServiceProvider.php`
- `bootstrap/app.php`
- `routes/api.php`
- `tests/Feature/ContentBriefApiTest.php`

## Validation

- `php -l app/Http/Controllers/Api/V1/Admin/ContentBriefController.php` ✅
- `php -l app/Modules/ContentBriefs/Services/GenerateContentBriefFromTopicService.php` ✅
- `php -l app/Modules/ContentBriefs/Repositories/EloquentContentBriefRepository.php` ✅
- `php -l tests/Feature/ContentBriefApiTest.php` ✅
- `php artisan migrate` ✅
- `php artisan test tests/Feature/ContentBriefApiTest.php` ✅
- `php artisan test tests/Feature/ContentTopicApiTest.php tests/Feature/ContentBriefApiTest.php tests/Feature/TopicDiscoveryAgentTest.php tests/Feature/TopicDiscoveryExecutionTest.php` ✅
- `php artisan test` ⚠️ failed in existing `Tests\Feature\ActivityLogTest::test_editorial_mutations_are_recorded_with_curated_audit_payloads` because media storage attempted to fetch instance-profile credentials from `169.254.169.254`

## Risks Or Follow-Ups

- brief generation in this story is deterministic service-side scaffolding from approved topics; the AI-driven `ContentBriefAgent` remains for `WB-SVC-050`
- content brief status `used` is supported in the model and update service for downstream workflow continuity, but no dedicated endpoint for draft-generation handoff exists yet
- full-suite validation remains blocked by the unrelated activity-log/media credential failure noted above

## Completion Notes

- content briefs now have first-class backend support with structured storage, topic-linked generation from approved topics, admin review APIs, approval gating, and structured image/internal-link suggestions without generating images
