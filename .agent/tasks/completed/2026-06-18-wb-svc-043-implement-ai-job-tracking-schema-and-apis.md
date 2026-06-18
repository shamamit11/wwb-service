# Task Summary

Implement `WB-SVC-043 — Implement AI job tracking schema and APIs`.

## Requested Outcome

- add persistent `ai_jobs` and `ai_generation_steps` tables
- add AI job and generation step models
- add repository and service layer for job lifecycle and reads
- add admin read and retry APIs
- keep retries explicit, tracked, and safe for later workflow idempotency

## Scope Boundaries

- in scope: schema, models, repositories, services, admin endpoints, request/resource classes, and tests for AI job tracking
- out of scope: full topic/brief/draft workflow orchestration, queue workers that execute AI jobs, or admin frontend changes

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/api-standards.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/queue-scheduler.md`
- `.agent/skills/laravel-api.md`
- `.agent/TESTING.md`
- `.agent/COMMANDS.md`
- `.agent/TASK-WORKFLOW.md`
- `docs/AI_CONTENT_ENGINE.md`
- `WB_SERVICE_AI_AGENTS_TASKS.md`

## Repository Files Inspected

- `routes/api.php`
- `app/Providers/AppServiceProvider.php`
- `app/Models/Post.php`
- `app/Models/Media.php`
- `app/Http/Controllers/Api/V1/Admin/CategoryController.php`
- `app/Http/Controllers/Api/V1/Admin/MediaController.php`
- `app/Http/Requests/Api/V1/Admin/ListMediaRequest.php`
- `app/Http/Resources/Api/ApiResource.php`
- `app/Http/Resources/Api/V1/MediaResource.php`
- `app/Modules/Categories/Repositories/CategoryRepository.php`
- `app/Modules/Categories/Repositories/EloquentCategoryRepository.php`
- `app/Modules/Media/Services/ListAdminMediaService.php`
- `app/Modules/Posts/Data/PostFiltersData.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Http/Requests/Api/V1/Admin/ListPostsRequest.php`
- `database/migrations/2026_06_16_173000_create_media_table.php`
- `database/migrations/2026_06_16_192000_create_knowledge_base_entries_table.php`
- `database/migrations/2026_06_17_194450_create_agent_conversations_table.php`
- `tests/Feature/CategoryApiTest.php`
- `tests/Feature/KnowledgeBaseApiTest.php`
- `tests/Feature/PostApiTest.php`

## Plan

1. Define AI job statuses and schema details aligned to the spec and existing models.
2. Implement migrations, models, repositories, and lifecycle services.
3. Add admin list, show, and retry endpoints with requests and resources.
4. Add feature tests for reads, retry behavior, and lifecycle persistence.
5. Run targeted validation plus `php artisan migrate` and note any broader test blockers.

## Changed Files

- `.agent/tasks/completed/2026-06-18-wb-svc-043-implement-ai-job-tracking-schema-and-apis.md`
- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Admin/AiJobController.php`
- `app/Http/Requests/Api/V1/Admin/ListAiJobsRequest.php`
- `app/Http/Requests/Api/V1/Admin/RetryAiJobRequest.php`
- `app/Http/Resources/Api/V1/AiGenerationStepResource.php`
- `app/Http/Resources/Api/V1/AiJobResource.php`
- `app/Models/AiGenerationStep.php`
- `app/Models/AiJob.php`
- `app/Models/Media.php`
- `app/Modules/Ai/Data/AiJobFiltersData.php`
- `app/Modules/Ai/Data/CreateAiGenerationStepData.php`
- `app/Modules/Ai/Data/CreateAiJobData.php`
- `app/Modules/Ai/Data/UpdateAiGenerationStepStatusData.php`
- `app/Modules/Ai/Data/UpdateAiJobStatusData.php`
- `app/Modules/Ai/Exceptions/AiJobRetryNotAllowedException.php`
- `app/Modules/Ai/Repositories/AiGenerationStepRepository.php`
- `app/Modules/Ai/Repositories/AiJobRepository.php`
- `app/Modules/Ai/Repositories/EloquentAiGenerationStepRepository.php`
- `app/Modules/Ai/Repositories/EloquentAiJobRepository.php`
- `app/Modules/Ai/Services/ListAdminAiJobsService.php`
- `app/Modules/Ai/Services/ReadAiJobService.php`
- `app/Modules/Ai/Services/RetryAiJobService.php`
- `app/Modules/Ai/Services/TrackAiJobService.php`
- `app/Providers/AppServiceProvider.php`
- `bootstrap/app.php`
- `database/migrations/2026_06_18_100000_create_ai_jobs_table.php`
- `database/migrations/2026_06_18_100100_create_ai_generation_steps_table.php`
- `routes/api.php`
- `tests/Feature/AiJobApiTest.php`
- `tests/Feature/AiJobTrackingServiceTest.php`

## Validation

- `php artisan test tests/Feature/AiJobApiTest.php tests/Feature/AiJobTrackingServiceTest.php` ✅
- `vendor/bin/pint --dirty` ✅
- `php artisan migrate` ✅
- `php artisan test` ❌ existing unrelated failure in `tests/Feature/ActivityLogTest.php::test_editorial_mutations_are_recorded_with_curated_audit_payloads`

## Risks Or Follow-Ups

- retry currently creates a new tracked queued job and preserves the failed original; actual downstream queue dispatch still needs to be wired when workflow jobs are implemented
- deduplication of posts/topics on retry is enforced here by retry lineage and not reusing domain records yet, but true end-to-end idempotency will need orchestration logic once topic/brief/draft generators exist

## Completion Notes

- Summary: added AI job and generation-step schema, models, lifecycle tracking services, repository bindings, admin read APIs, and a safe failed-job retry endpoint.
- Changed files: schema, models, `app/Modules/Ai`, admin API classes, route registration, and feature tests listed above.
- Validation run: targeted AI job tests passed, migrations applied cleanly, and formatting passed; full suite still hits the existing AWS credential failure in `ActivityLogTest`.
- Risks: full retry execution is not dispatched yet because the concrete AI workflow jobs have not been implemented in this phase.
- Follow-ups: wire future topic/brief/draft queue jobs into `TrackAiJobService`, and consider exposing prompt/version metadata in `ai_jobs` once prompt management lands.
