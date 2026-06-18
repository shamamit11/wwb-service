# Task Summary

Implement `WB-SVC-044 — Implement AI token and cost tracking`.

## Requested Outcome

- add `ai_job_costs` persistence
- add a usage recording service
- track provider/model/token usage per job and per step
- support estimated cost calculation even when providers do not return billed cost

## Scope Boundaries

- in scope: migration, model, repository, service, AI config pricing support, AI job read exposure, and tests for usage and cost tracking
- out of scope: provider-side billed cost reconciliation, billing exports, or admin frontend changes

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

- `config/ai.php`
- `app/Infrastructure/Ai/Data/AiUsageData.php`
- `app/Infrastructure/Ai/Data/TextGenerationResult.php`
- `app/Http/Resources/Api/V1/AiJobResource.php`
- `app/Http/Resources/Api/V1/AiGenerationStepResource.php`
- `app/Http/Requests/Api/V1/Admin/ListAiJobsRequest.php`
- `app/Modules/Ai/Repositories/EloquentAiJobRepository.php`
- `app/Modules/Ai/Services/TrackAiJobService.php`
- `app/Models/AiJob.php`
- `app/Models/AiGenerationStep.php`
- `tests/Feature/AiJobApiTest.php`
- `tests/Feature/AiJobTrackingServiceTest.php`

## Plan

1. Add `ai_job_costs` schema and model relationships.
2. Implement a usage and cost recording service with config-driven price estimation.
3. Expose cost data in AI job reads and extend provider/model filtering where useful.
4. Add tests for sparse usage handling, estimation, and API visibility.
5. Run targeted validation, `php artisan migrate`, then the broader test command and note existing blockers.

## Changed Files

- `.agent/tasks/completed/2026-06-18-wb-svc-044-implement-ai-token-and-cost-tracking.md`
- `.agent/tasks/current-task.md`
- `app/Http/Requests/Api/V1/Admin/ListAiJobsRequest.php`
- `app/Http/Resources/Api/V1/AiGenerationStepResource.php`
- `app/Http/Resources/Api/V1/AiJobCostResource.php`
- `app/Http/Resources/Api/V1/AiJobResource.php`
- `app/Models/AiGenerationStep.php`
- `app/Models/AiJob.php`
- `app/Models/AiJobCost.php`
- `app/Modules/Ai/Data/AiJobFiltersData.php`
- `app/Modules/Ai/Data/CreateAiJobCostData.php`
- `app/Modules/Ai/Repositories/AiJobCostRepository.php`
- `app/Modules/Ai/Repositories/EloquentAiJobCostRepository.php`
- `app/Modules/Ai/Repositories/EloquentAiJobRepository.php`
- `app/Modules/Ai/Services/RecordAiUsageService.php`
- `app/Providers/AppServiceProvider.php`
- `config/ai.php`
- `database/migrations/2026_06_18_110000_create_ai_job_costs_table.php`
- `tests/Feature/AiJobApiTest.php`
- `tests/Feature/AiUsageCostTrackingServiceTest.php`

## Validation

- `php artisan test tests/Feature/AiJobApiTest.php tests/Feature/AiJobTrackingServiceTest.php tests/Feature/AiUsageCostTrackingServiceTest.php` ✅
- `vendor/bin/pint --dirty` ✅
- `php artisan migrate` ✅
- `php artisan test` ❌ existing unrelated failure in `tests/Feature/ActivityLogTest.php::test_editorial_mutations_are_recorded_with_curated_audit_payloads`

## Risks Or Follow-Ups

- estimated cost accuracy depends on config-maintained pricing tables and may need future revision when provider pricing changes
- current pricing config ships with a small starter matrix and will need expansion as additional providers/models enter active use

## Completion Notes

- Summary: added normalized AI usage/cost persistence, a resilient recording service, config-driven price estimation, and cost exposure on AI job reads with provider/model query support.
- Changed files: migration, cost model/repository/service, AI job resource/request updates, config pricing, and feature tests listed above.
- Validation run: focused AI usage/cost tests passed, formatter passed, migration succeeded, and the full suite still hits the existing AWS credential issue in `ActivityLogTest`.
- Risks: missing or outdated price config will reduce estimate quality but will not fail workflows.
- Follow-ups: wire `RecordAiUsageService` into future concrete agent/workflow execution paths so each provider call automatically records step-level and aggregate job cost data.
