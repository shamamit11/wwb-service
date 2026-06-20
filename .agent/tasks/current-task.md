# Task Record

## Task Summary

Queue content brief generation automatically when an admin approves a topic, while preserving manual generation and safe brief deduplication.

## Requested Outcome

- approving a topic should enqueue content brief generation automatically
- keep topic approval separate from synchronous AI execution
- avoid duplicate content briefs when one already exists
- add focused tests for approval-triggered queueing

## Scope Boundaries

- service repository only
- topic approval and content brief queueing behavior only
- no admin app changes
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

## Plan

1. Add a queued content brief dispatch path that is safe when a brief already exists.
2. Trigger that queue path from topic approval.
3. Add focused approval-flow tests and run the smallest relevant test selection.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Modules/Ai/Services/ContentBriefWorkflow.php`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Modules/ContentTopics/Services/ApproveContentTopicService.php`
- `tests/Feature/ContentTopicApiTest.php`

## Validation

- `php artisan test --filter=ContentTopicApiTest` passed
- `php artisan test --filter=ContentBriefApiTest` passed

## Risks Or Follow-Ups

- approval-triggered queueing can create additional AI jobs, so admin UI may later want to surface the queued job status next to the approved topic

## Completion Notes

- Topic approval now enqueues `GenerateContentBriefJob` onto the `ai` queue through `ContentBriefWorkflow::queue()`.
- The queue path is idempotent for topics that already have a brief: approval still succeeds, but no new AI job is created.
- Manual `Generate Brief` remains available and unchanged.
