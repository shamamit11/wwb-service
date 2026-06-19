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
- `app/Http/Controllers/Api/V1/Admin/PostController.php`
- `app/Http/Requests/Api/V1/Admin/QueuePostMetadataSuggestionRequest.php`
- `app/Modules/Ai/Data/QueuePostMetadataSuggestionData.php`
- `app/Modules/Ai/Services/MetadataSuggestionWorkflow.php`
- `app/Modules/Ai/Services/SuggestPostMetadataService.php`
- `app/Mcp/Tools/SuggestPostMetadataTool.php`
- `app/Mcp/Prompts/MetadataSuggestionPrompt.php`
- `app/Mcp/Servers/ContentOperationsServer.php`
- `routes/api.php`
- `tests/Feature/PostApiTest.php`
- `tests/Feature/ContentOperationsMcpServerTest.php`
- `tests/Feature/PostMetadataSuggestionWorkflowTest.php`

## Plan

1. Inspect the existing post metadata and rewrite workflows to identify the cleanest place for title/excerpt refinement orchestration.
2. Add the refinement contract, workflow plumbing, MCP exposure, and test coverage while keeping suggestions review-only.
3. Validate with focused tests and full `php artisan test`, then archive/commit/push.

## Changed Files

- `PHASE2.md`
- `.agent/tasks/current-task.md`
- `app/AI/Agents/TitleExcerptRefinementAgent.php`
- `app/AI/DTO/PostTitleExcerptRefinementInput.php`
- `app/AI/DTO/PostTitleExcerptRefinementResult.php`
- `app/Http/Controllers/Api/V1/Admin/PostController.php`
- `app/Http/Requests/Api/V1/Admin/QueuePostTitleExcerptRefinementRequest.php`
- `app/Jobs/AI/GeneratePostTitleExcerptRefinementJob.php`
- `app/Mcp/Prompts/TitleExcerptRefinementPrompt.php`
- `app/Mcp/Servers/ContentOperationsServer.php`
- `app/Mcp/Tools/RefinePostTitleExcerptTool.php`
- `app/Models/AiPromptTemplate.php`
- `app/Modules/Ai/Data/QueuePostTitleExcerptRefinementData.php`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Modules/Ai/Services/QueuePostTitleExcerptRefinementService.php`
- `app/Modules/Ai/Services/RunPostTitleExcerptRefinementService.php`
- `app/Modules/Ai/Services/SuggestPostTitleExcerptRefinementService.php`
- `app/Modules/Ai/Services/TitleExcerptRefinementWorkflow.php`
- `routes/api.php`
- `tests/Feature/ContentOperationsMcpServerTest.php`
- `tests/Feature/PostApiTest.php`
- `tests/Feature/PostTitleExcerptRefinementWorkflowTest.php`

## Validation

- `php artisan test tests/Feature/PostTitleExcerptRefinementWorkflowTest.php`
- `php artisan test tests/Feature/PostApiTest.php --filter=title_excerpt_refinement`
- `php artisan test tests/Feature/ContentOperationsMcpServerTest.php`
- `php artisan test`

## Risks Or Follow-Ups

- title/excerpt refinement remains review-only by design; applying a chosen suggestion to the post should stay a separate editorial action unless explicitly requested
- this workflow introduces a dedicated AI prompt type to keep retries unambiguous and avoid conflating refinement with rewrite or SEO-metadata runs

## Completion Notes

- Summary: Added a review-only title and excerpt refinement workflow for posts and drafts across admin API, MCP, AI jobs, and prompt-backed agent execution.
- Changed files: new refinement agent/DTOs/workflow services, admin API request/controller/route, MCP tool/prompt/server registration, prompt type update, tests, and phase summary.
- Validation run: focused refinement workflow/API/MCP tests plus full `php artisan test` suite passed.
- Admin impact: additive only. New optional workflow endpoint is `POST /api/v1/admin/posts/{post}/refine-title-excerpt`.
- Result: the remaining in-phase AI-assisted publishing service backlog is now cleared, excluding explicitly deferred newsletter and next-phase agent work.
