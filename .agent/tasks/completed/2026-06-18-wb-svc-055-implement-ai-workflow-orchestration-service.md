# Task Summary

Implement `WB-SVC-055` by introducing an AI workflow orchestration layer that coordinates topic discovery, content brief generation, draft generation, and retry-safe AI job execution.

## Requested Outcome

- add `AiWorkflowOrchestrator`
- add `TopicDiscoveryWorkflow`
- add `ContentBriefWorkflow`
- add `DraftGenerationWorkflow`
- centralize AI job dispatch/run/retry coordination outside controllers
- keep retry behavior safe and idempotent

## Scope Boundaries

- in scope: `app/Modules/Ai/Services`, AI jobs, controller/service wiring, targeted tests
- out of scope: sibling apps, UI work, unrelated AI provider changes

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/ai-content-engine.md`
- `.agent/TESTING.md`
- `.agent/COMMANDS.md`
- `.agent/skills/testing.md`
- `.agent/skills/service-testing-matrix.md`
- `docs/AI_CONTENT_ENGINE.md`

## Repository Files Inspected

- `app/Console/Commands/DiscoverContentTopicsCommand.php`
- `app/Http/Controllers/Api/V1/Admin/ContentTopicController.php`
- `app/Http/Controllers/Api/V1/Admin/ContentBriefController.php`
- `app/Http/Controllers/Api/V1/Admin/AiJobController.php`
- `app/Jobs/AI/DiscoverContentTopicsJob.php`
- `app/Jobs/AI/GenerateBlogDraftJob.php`
- `app/Modules/Ai/Services/RunTopicDiscoveryService.php`
- `app/Modules/Ai/Services/RunBlogDraftGenerationService.php`
- `app/Modules/Ai/Services/QueueBlogDraftGenerationService.php`
- `app/Modules/Ai/Services/RetryAiJobService.php`
- `app/Modules/Ai/Services/TrackAiJobService.php`
- `app/Modules/Ai/Repositories/AiJobRepository.php`
- `app/Modules/Ai/Repositories/EloquentAiJobRepository.php`
- `app/Modules/ContentBriefs/Services/GenerateContentBriefFromTopicService.php`
- `app/Modules/Posts/Services/GenerateBlogDraftFromBriefService.php`
- `app/AI/Agents/TopicDiscoveryAgent.php`
- `app/AI/Agents/ContentBriefAgent.php`
- `app/AI/Agents/BlogWriterAgent.php`
- `tests/Feature/TopicDiscoveryExecutionTest.php`
- `tests/Feature/GenerateBlogDraftJobTest.php`
- `tests/Feature/ContentBriefApiTest.php`
- `tests/Feature/AiJobApiTest.php`

## Plan

1. Add workflow/orchestrator services and route topic discovery, brief generation, and draft generation through them.
2. Update agents and jobs so queued workflows reuse pre-created AI jobs instead of creating nested ones.
3. Add targeted coverage for orchestration, retry dispatch, and full-suite validation.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Modules/Ai/Services/TopicDiscoveryWorkflow.php`
- `app/Modules/Ai/Services/ContentBriefWorkflow.php`
- `app/Modules/Ai/Services/DraftGenerationWorkflow.php`
- `app/Modules/Ai/Services/RunTopicDiscoveryService.php`
- `app/Modules/Ai/Services/RunBlogDraftGenerationService.php`
- `app/Modules/Ai/Services/QueueBlogDraftGenerationService.php`
- `app/Modules/Ai/Services/RetryAiJobService.php`
- `app/Modules/ContentBriefs/Services/GenerateContentBriefFromTopicService.php`
- `app/Console/Commands/DiscoverContentTopicsCommand.php`
- `app/Http/Controllers/Api/V1/Admin/ContentTopicController.php`
- `app/Jobs/AI/DiscoverContentTopicsJob.php`
- `app/Jobs/AI/GenerateContentBriefJob.php`
- `app/Jobs/AI/GenerateBlogDraftJob.php`
- `app/AI/Agents/TopicDiscoveryAgent.php`
- `app/AI/Agents/ContentBriefAgent.php`
- `tests/Feature/TopicDiscoveryExecutionTest.php`
- `tests/Feature/AiJobApiTest.php`

## Validation

- `php artisan test --filter='(TopicDiscoveryExecutionTest|AiJobApiTest|GenerateBlogDraftJobTest|ContentBriefApiTest)'`
- `php artisan test`

Result:

- targeted orchestration and retry coverage passed
- full Laravel test suite passed: `155` tests, `1263` assertions

## Risks Or Follow-Ups

- content brief generation is still synchronous for the primary admin endpoint; the new queued brief job path currently exists to support orchestration consistency and retries rather than a new public async endpoint

## Completion Notes

- added a top-level orchestration layer plus dedicated topic discovery, content brief, and draft generation workflows
- queued topic discovery now creates an `ai_jobs` record before dispatch, matching the existing queued draft pattern
- queued topic discovery and queued content-brief execution now reuse pre-created AI jobs instead of creating nested jobs inside agents
- retry dispatch is now workflow-aware for topic discovery, content brief, and blog writer job types
- controllers and console entrypoints remain thin and delegate orchestration to services
