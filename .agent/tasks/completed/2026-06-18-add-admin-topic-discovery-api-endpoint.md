# Task Summary

Implement the missing admin topic discovery API endpoint and align the checked-in service contract with the real route surface.

## Requested Outcome

- add an authenticated admin API endpoint to queue topic discovery work
- keep the endpoint aligned with the existing controller -> request -> DTO -> service -> resource pattern
- return the queued AI job using the existing AI job response contract
- update the checked-in contract docs so topic discovery is represented accurately

## Scope Boundaries

- in scope: admin route, controller/request/service wiring, tests, and narrow contract doc updates for topic discovery
- out of scope: sibling admin app changes, broad OpenAPI cleanup outside the touched topic discovery section, and synchronous discovery over HTTP

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/api-standards.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/api-contracts.md`

## Repository Files Inspected

- `routes/api.php`
- `docs/OPENAPI_SPEC.md`
- `app/Http/Controllers/Api/V1/Admin/AiJobController.php`
- `app/Http/Controllers/Api/V1/Admin/ContentBriefController.php`
- `app/Http/Requests/Api/V1/Admin/GenerateBlogDraftRequest.php`
- `app/Http/Resources/Api/V1/AiJobResource.php`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Modules/Ai/Services/QueueBlogDraftGenerationService.php`
- `app/Modules/Ai/Services/RunTopicDiscoveryService.php`
- `app/Modules/Ai/Services/TopicDiscoveryWorkflow.php`
- `app/Modules/Ai/Data/DiscoverContentTopicsData.php`
- `app/Console/Commands/DiscoverContentTopicsCommand.php`
- `tests/Feature/AiJobApiTest.php`
- `tests/Feature/TopicDiscoveryExecutionTest.php`

## Plan

1. Add a request/service/controller path for queued admin topic discovery and register the route.
2. Add feature coverage for authentication, validation, and successful queued dispatch.
3. Update the checked-in contract doc section for AI jobs and validate with focused plus full tests.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Admin/AiJobController.php`
- `app/Http/Requests/Api/V1/Admin/DiscoverContentTopicsRequest.php`
- `app/Modules/Ai/Services/QueueTopicDiscoveryService.php`
- `routes/api.php`
- `tests/Feature/AiJobApiTest.php`
- `docs/OPENAPI_SPEC.md`

## Validation

- `php artisan test tests/Feature/AiJobApiTest.php`
- `php artisan test`

## Risks Or Follow-Ups

- `docs/OPENAPI_SPEC.md` contains broader legacy path mismatches outside topic discovery; this task will only correct the touched section unless more cleanup is requested.

## Completion Notes

- Added `POST /api/v1/admin/ai-jobs/topic-discovery` behind the existing admin auth middleware.
- The endpoint uses `DiscoverContentTopicsRequest -> DiscoverContentTopicsData -> QueueTopicDiscoveryService -> AiWorkflowOrchestrator::dispatchTopicDiscovery()` and returns the standard `AiJobResource` envelope with `202 Accepted`.
- Updated the checked-in AI jobs contract section to document the new topic discovery request and response shape.
