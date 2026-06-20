# Task Record

## Task Summary

Convert manual `Generate Brief` into a queued workflow so admin requests no longer block on long AI calls or hit PHP execution time limits.

## Requested Outcome

- stop `POST /api/v1/admin/content-topics/{id}/generate-brief` from running AI generation inside the HTTP request
- queue or reuse a content brief AI job instead
- still return an existing brief immediately when one already exists
- avoid duplicate queued brief jobs from repeated clicks
- add focused tests for the new endpoint behavior

## Scope Boundaries

- service repository only
- content brief generation endpoint behavior and targeted tests only
- no admin app changes unless required by confirmed contract break
- no provider integration changes

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/ai-content-engine.md`
- `../admin/.agent/API-CONTRACT.md`
- `../admin/.agent/ARCHITECTURE.md`

## Repository Files Inspected

- `app/Http/Controllers/Api/V1/Admin/ContentTopicController.php`
- `app/Modules/Ai/Services/ContentBriefWorkflow.php`
- `app/Modules/ContentBriefs/Services/GenerateContentBriefFromTopicService.php`
- `app/AI/Agents/ContentBriefAgent.php`
- `app/AI/DTO/AgentResult.php`
- `app/AI/DTO/AgentErrorData.php`
- `app/Infrastructure/Ai/LaravelAiClient.php`
- `app/Infrastructure/Ai/Exceptions/AiCallFailedException.php`
- `app/Modules/Ai/Services/TrackAiJobService.php`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Modules/Ai/Services/ContentBriefWorkflow.php`
- `app/Jobs/AI/GenerateContentBriefJob.php`
- `app/Modules/ContentTopics/Services/ApproveContentTopicService.php`
- `bootstrap/app.php`
- `app/Support/ApiErrorResponse.php`
- `config/ai.php`
- `tests/Feature/ContentTopicApiTest.php`
- `tests/Feature/ContentBriefApiTest.php`
- `../admin/app/Services/WideWebBlogApi/Clients/ContentTopicClient.php`
- `../admin/app/Livewire/Admin/TopicQueue/Show.php`
- `../admin/tests/Feature/TopicQueue/TopicQueueScreensTest.php`

## Plan

1. Confirm the admin `Generate Brief` contract and narrowest safe response shape.
2. Change the endpoint to queue or reuse a brief AI job instead of doing synchronous generation.
3. Add focused endpoint tests and run the smallest relevant test selection.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Admin/ContentTopicController.php`
- `app/Modules/Ai/Services/ContentBriefWorkflow.php`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Modules/ContentTopics/Services/ApproveContentTopicService.php`
- `tests/Feature/ContentTopicApiTest.php`
- `tests/Feature/ContentBriefApiTest.php`

## Validation

- `php artisan test --filter=ContentBriefApiTest` passed
- `php artisan test --filter=ContentTopicApiTest` passed

## Risks Or Follow-Ups

- admin UI currently redirects to brief details when `data.id` is present, so queued responses intentionally avoid `data.id` until a brief actually exists

## Completion Notes

- Root cause confirmed from production error stack: the manual `generate-brief` endpoint still used `ContentBriefWorkflow::generate()`, which ran the AI provider call inside the HTTP request until FrankenPHP hit the 30-second execution limit.
- `POST /api/v1/admin/content-topics/{id}/generate-brief` now queues or reuses a content brief AI job and returns `202 Accepted` with `meta.ai_job_id` when no brief exists yet.
- If a brief already exists, the endpoint still returns that brief immediately with `200 OK`.
- Repeated clicks while a brief job is already pending, queued, or processing now reuse the active job instead of creating duplicate jobs.
