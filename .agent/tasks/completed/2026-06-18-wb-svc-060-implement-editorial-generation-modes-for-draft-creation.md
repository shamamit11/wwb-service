# Task Record

## Task Summary

Implement `WB-SVC-060` by adding explicit editorial generation modes for AI draft creation.

## Requested Outcome

- add a mode-aware draft generation contract for supported article styles
- expose the mode selection through admin API and MCP
- keep the default generation path backward compatible when no mode is provided
- ensure mode selection changes prompt/template behavior without changing draft-only review rules

## Scope Boundaries

- in scope: service-side workflow, DTOs, validation, API route updates if needed, MCP updates if needed, AI workflow plumbing, and tests
- out of scope: admin UI implementation, title/excerpt refinement tooling, publishing behavior, and newsletter work

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
- `app/AI/Agents/BlogWriterAgent.php`
- `app/AI/DTO/BlogDraftInput.php`
- `app/Http/Controllers/Api/V1/Admin/ContentBriefController.php`
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

## Plan

1. Inspect the existing blog draft generation DTOs, workflow, and prompt rendering path to determine the cleanest place for mode-aware generation.
2. Add the generation-mode contract, workflow plumbing, and test coverage while preserving the default draft path.
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

- generation modes remain prompt-driven by design; if future modes require different persistence or approval rules, that should be treated as a separate workflow design task
- mode-specific prompt steering is strongest when matching prompt templates exist; otherwise the workflow still passes the mode and guidance through the standard prompt variables

## Completion Notes

- Summary: Added explicit editorial generation modes for draft creation across admin API, MCP, queued AI jobs, prompt variables, and retry-safe workflow plumbing.
- Changed files: new generation-mode enum plus request/data/agent/workflow/MCP/test updates and phase tracking.
- Validation run: focused draft workflow tests plus full `php artisan test` suite passed.
- Admin impact: additive only. Existing generate-draft consumers keep working; new optional request field is `generation_mode`.
- Follow-ups: start `WB-SVC-061` for title and excerpt refinement tools.
