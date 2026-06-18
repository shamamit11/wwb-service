# Task Record

## Task Summary

Implement `WB-SVC-058` by adding a first-class AI draft rewrite and section regeneration workflow for existing draft posts.

## Requested Outcome

- add a retry-safe rewrite workflow for existing draft posts
- support full-draft, section, and paragraph rewrite scopes
- expose the workflow through admin API and MCP
- track rewrite runs through `ai_jobs` and `ai_generation_steps`
- keep all rewrite output review-only and draft-only

## Scope Boundaries

- in scope: service-side workflow, DTOs, request validation, API route, MCP tool/prompt, AI job execution, persistence, and tests
- out of scope: admin UI implementation, publishing behavior, newsletter work, and later metadata/title refinement tasks

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/ai-content-engine.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/testing.md`

## Repository Files Inspected

- `WB_SERVICE_AI_AGENTS_TASKS.md`
- `PHASE2.md`
- `routes/api.php`
- `bootstrap/app.php`
- `app/Http/Controllers/Api/V1/Admin/PostController.php`
- `app/Http/Requests/Api/V1/Admin/QueuePostRewriteRequest.php`
- `app/AI/Agents/DraftRewriteAgent.php`
- `app/AI/DTO/PostRewriteInput.php`
- `app/AI/DTO/PostRewriteResult.php`
- `app/Jobs/AI/GeneratePostRewriteJob.php`
- `app/Modules/Ai/Data/QueuePostRewriteData.php`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Modules/Ai/Services/DraftRewriteWorkflow.php`
- `app/Modules/Ai/Services/QueuePostRewriteService.php`
- `app/Modules/Ai/Services/RunPostRewriteService.php`
- `app/Modules/Posts/Exceptions/PostRewriteNotAllowedException.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Services/ReadAdminPostService.php`
- `app/Modules/Posts/Services/RewritePostDraftService.php`
- `app/Mcp/Servers/ContentOperationsServer.php`
- `app/Mcp/Tools/RewritePostDraftTool.php`
- `app/Mcp/Prompts/DraftRewritePrompt.php`
- `tests/Feature/PostApiTest.php`
- `tests/Feature/PostRewriteWorkflowTest.php`
- `tests/Feature/ContentOperationsMcpServerTest.php`

## Plan

1. Add rewrite DTOs, workflow services, AI job execution job, and persistence support for full and partial rewrites.
2. Expose rewrite through admin API and MCP with validation and prompt wiring.
3. Add focused feature tests, run the smallest meaningful test set, then archive/commit/push if green.

## Changed Files

- `PHASE2.md`
- `app/Http/Controllers/Api/V1/Admin/PostController.php`
- `app/Http/Requests/Api/V1/Admin/QueuePostRewriteRequest.php`
- `app/AI/Agents/DraftRewriteAgent.php`
- `app/AI/DTO/PostRewriteInput.php`
- `app/AI/DTO/PostRewriteResult.php`
- `app/Jobs/AI/GeneratePostRewriteJob.php`
- `app/Modules/Ai/Data/QueuePostRewriteData.php`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Modules/Ai/Services/DraftRewriteWorkflow.php`
- `app/Modules/Ai/Services/QueuePostRewriteService.php`
- `app/Modules/Ai/Services/RunPostRewriteService.php`
- `app/Modules/Posts/Exceptions/PostRewriteNotAllowedException.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Services/ReadAdminPostService.php`
- `app/Modules/Posts/Services/RewritePostDraftService.php`
- `app/Mcp/Servers/ContentOperationsServer.php`
- `app/Mcp/Tools/RewritePostDraftTool.php`
- `app/Mcp/Prompts/DraftRewritePrompt.php`
- `bootstrap/app.php`
- `routes/api.php`
- `tests/Feature/PostApiTest.php`
- `tests/Feature/PostRewriteWorkflowTest.php`
- `tests/Feature/ContentOperationsMcpServerTest.php`

## Validation

- `php artisan test tests/Feature/PostRewriteWorkflowTest.php`
- `php artisan test tests/Feature/PostApiTest.php --filter=draft_rewrite`
- `php artisan test tests/Feature/ContentOperationsMcpServerTest.php`
- `php artisan test`

## Risks Or Follow-Ups

- section regeneration is block-based and requires contiguous target blocks rather than semantic document parsing
- rewrite support currently assumes AI-generated drafts with source brief/topic provenance; extending the same workflow to manual drafts would need broader context rules

## Completion Notes

- Summary: Added queued rewrite/regeneration workflows for existing AI-generated draft posts across admin API, MCP, AI jobs, and persistence.
- Changed files: rewrite agent/DTOs, workflow services, admin API request/controller/route, MCP tool/prompt/server registration, read service/repository support, exception mapping, tests, and phase summary.
- Validation run: focused rewrite/API/MCP tests plus full `php artisan test` suite passed.
- Risks: section semantics remain block-oriented rather than document-outline aware.
- Follow-ups: start `WB-SVC-059` for explicit metadata suggestion workflow.
