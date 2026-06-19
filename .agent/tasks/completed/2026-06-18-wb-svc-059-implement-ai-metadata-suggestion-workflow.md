# Task Record

## Task Summary

Implement `WB-SVC-059` by adding an explicit AI metadata suggestion workflow for existing drafts and posts.

## Requested Outcome

- add a dedicated metadata suggestion service for title, excerpt, meta title, meta description, and focus keyword
- expose the workflow through admin API and MCP
- keep all outputs review-only and non-publishing
- track metadata suggestion runs through `ai_jobs` and `ai_generation_steps`

## Scope Boundaries

- in scope: service-side workflow, DTOs, request validation, API route, MCP tool/prompt, AI job execution, persistence, and tests
- out of scope: admin UI implementation, auto-application of suggestions, publishing behavior, and later generation-mode/title-refinement tasks

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/ai-content-engine.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/testing.md`

## Repository Files Inspected

- `PHASE2.md`
- `WB_SERVICE_AI_AGENTS_TASKS.md`
- `app/Http/Controllers/Api/V1/Admin/SeoMetadataController.php`
- `app/Http/Requests/Api/V1/Admin/UpdateSeoMetadataRequest.php`
- `app/Modules/Seo/Services/ReadSeoMetadataService.php`
- `app/Modules/Seo/Services/UpsertSeoMetadataService.php`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Mcp/Prompts/SeoReviewPrompt.php`
- `app/Mcp/Servers/ContentOperationsServer.php`
- `app/Http/Controllers/Api/V1/Admin/PostController.php`
- `routes/api.php`
- `tests/Feature/PostApiTest.php`
- `tests/Feature/ContentOperationsMcpServerTest.php`

## Plan

1. Inspect the existing SEO metadata, post, and AI workflow services to find the cleanest place for metadata suggestion orchestration.
2. Add the metadata suggestion workflow, queue path, admin API contract, MCP surface, and tests.
3. Validate with focused tests and full `php artisan test`, then archive/commit/push.

## Changed Files

- `PHASE2.md`
- `app/AI/Agents/MetadataSuggestionAgent.php`
- `app/AI/DTO/PostMetadataSuggestionInput.php`
- `app/AI/DTO/PostMetadataSuggestionResult.php`
- `app/Http/Controllers/Api/V1/Admin/PostController.php`
- `app/Http/Requests/Api/V1/Admin/QueuePostMetadataSuggestionRequest.php`
- `app/Jobs/AI/GeneratePostMetadataSuggestionsJob.php`
- `app/Mcp/Prompts/MetadataSuggestionPrompt.php`
- `app/Mcp/Servers/ContentOperationsServer.php`
- `app/Mcp/Tools/SuggestPostMetadataTool.php`
- `app/Modules/Ai/Data/QueuePostMetadataSuggestionData.php`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Modules/Ai/Services/MetadataSuggestionWorkflow.php`
- `app/Modules/Ai/Services/QueuePostMetadataSuggestionService.php`
- `app/Modules/Ai/Services/RunPostMetadataSuggestionService.php`
- `app/Modules/Ai/Services/SuggestPostMetadataService.php`
- `routes/api.php`
- `tests/Feature/ContentOperationsMcpServerTest.php`
- `tests/Feature/PostApiTest.php`
- `tests/Feature/PostMetadataSuggestionWorkflowTest.php`

## Validation

- `php artisan test tests/Feature/PostMetadataSuggestionWorkflowTest.php`
- `php artisan test tests/Feature/PostApiTest.php --filter=metadata_suggestions`
- `php artisan test tests/Feature/ContentOperationsMcpServerTest.php`
- `php artisan test`

## Risks Or Follow-Ups

- metadata suggestions are intentionally review-only in this task; applying them to post or SEO records should remain a separate editorial action
- the current suggestion context primarily uses post content and existing SEO fields; richer brief provenance can be added later if the service starts persisting more brief context onto posts

## Completion Notes

- Summary: Added a review-only metadata suggestion workflow for posts and drafts across admin API, MCP, AI jobs, and prompt-backed agent execution.
- Changed files: metadata suggestion agent/DTOs/workflow services, admin API request/controller/route, MCP tool/prompt/server registration, tests, and phase summary.
- Validation run: focused metadata workflow/API/MCP tests plus full `php artisan test` suite passed.
- Risks: suggestion output currently lives in AI job output rather than a separate persisted suggestion model.
- Follow-ups: start `WB-SVC-060` for explicit editorial generation modes.
