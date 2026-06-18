# Task Record

## Task Summary

Implement `WB-SVC-061` by adding explicit AI title and excerpt refinement tools for editors.

## Requested Outcome

- add a dedicated refinement workflow for title suggestions, excerpt suggestions, and alternate headline variants
- expose the workflow through admin API and MCP
- keep the workflow review-only and non-publishing
- track refinement runs through `ai_jobs` and `ai_generation_steps`

## Scope Boundaries

- in scope: service-side workflow, DTOs, validation, API contract, MCP updates, AI job execution, and tests
- out of scope: admin UI implementation, auto-applying suggestions, publishing behavior, and newsletter work

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/api-standards.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/api-contracts.md`

## Repository Files Inspected

- `PHASE2.md`
- `WB_SERVICE_AI_AGENTS_TASKS.md`

## Plan

1. Inspect the existing post metadata and rewrite workflows to identify the cleanest place for title/excerpt refinement orchestration.
2. Add the refinement contract, workflow plumbing, MCP exposure, and test coverage while keeping suggestions review-only.
3. Validate with focused tests and full `php artisan test`, then archive/commit/push.

## Changed Files

- `PHASE2.md`
- `.agent/tasks/current-task.md`
- `app/AI/Agents/BlogWriterAgent.php`
- `app/AI/DTO/BlogDraftInput.php`
- `app/AI/Enums/BlogDraftGenerationMode.php`
- `app/Http/Requests/Api/V1/Admin/GenerateBlogDraftRequest.php`
- `app/Mcp/Prompts/BlogDraftPrompt.php`
- `app/Mcp/Tools/GenerateBlogDraftTool.php`
- `app/Modules/Ai/Data/QueueBlogDraftGenerationData.php`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Modules/Ai/Services/DraftGenerationWorkflow.php`
- `app/Modules/Posts/Services/GenerateBlogDraftFromBriefService.php`
- `tests/Feature/AiContentAgentContractsTest.php`
- `tests/Feature/BlogWriterAgentTest.php`
- `tests/Feature/ContentBriefApiTest.php`
- `tests/Feature/ContentOperationsMcpServerTest.php`
- `tests/Feature/GenerateBlogDraftJobTest.php`

## Validation

- `php artisan test tests/Feature/BlogWriterAgentTest.php`
- `php artisan test tests/Feature/ContentBriefApiTest.php`
- `php artisan test tests/Feature/ContentOperationsMcpServerTest.php`
- `php artisan test tests/Feature/GenerateBlogDraftJobTest.php`
- `php artisan test tests/Feature/AiContentAgentContractsTest.php`
- `php artisan test`

## Risks Or Follow-Ups

- title and excerpt refinement should stay review-only; applying suggestions should remain an explicit editorial action unless requested later

## Completion Notes

- `WB-SVC-060` completed and archived in `.agent/tasks/completed/2026-06-18-wb-svc-060-implement-editorial-generation-modes-for-draft-creation.md`
- implementation for `WB-SVC-061` not started yet
