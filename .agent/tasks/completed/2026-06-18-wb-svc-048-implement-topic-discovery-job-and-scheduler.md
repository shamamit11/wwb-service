# Task Summary

Implement `WB-SVC-048 — Implement topic discovery job and scheduler`.

## Requested Outcome

- add `DiscoverContentTopicsJob`
- use the explicit `ai` queue
- add a console command for manual topic discovery
- register scheduler execution
- keep topic creation retry-safe with no auto-approval

## Scope Boundaries

- in scope: queued topic discovery execution, command orchestration, scheduler registration, and validation/tests
- out of scope: admin UI changes, non-topic AI workflows, or auto-approval behavior

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/ai-content-engine.md`
- `.agent/skills/queue-scheduler.md`
- `.agent/skills/testing.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `docs/AI_CONTENT_ENGINE.md`
- `WB_SERVICE_AI_AGENTS_TASKS.md`

## Repository Files Inspected

- `routes/console.php`
- `bootstrap/app.php`
- `composer.json`
- `config/queue.php`
- `config/ai.php`
- `app/AI/Agents/TopicDiscoveryAgent.php`
- `app/Modules/Ai/Services/RetryAiJobService.php`
- `app/Models/KnowledgeBaseEntry.php`
- `app/Modules/KnowledgeBase/Repositories/KnowledgeBaseEntryRepository.php`
- `app/Modules/KnowledgeBase/Repositories/EloquentKnowledgeBaseEntryRepository.php`
- `app/Modules/KnowledgeBase/Data/KnowledgeBaseEntryFiltersData.php`
- `app/Modules/ContentTopics/Services/ListAdminContentTopicsService.php`
- `app/Modules/ContentTopics/Data/ContentTopicFiltersData.php`
- `app/Models/ContentTopic.php`
- `tests/Feature/TopicDiscoveryAgentTest.php`

## Plan

1. Add a reusable orchestration path that builds topic-discovery input from current topic and knowledge-base state.
2. Add the queued job and console command, both routed to the explicit `ai` queue.
3. Register a scheduler entry that dispatches discovery safely.
4. Add focused tests for command execution, queue dispatch, scheduler visibility, and retry-safe topic creation.
5. Run targeted validation plus the requested scheduler and test commands, then record any existing unrelated failures.

## Changed Files

- `.agent/tasks/completed/2026-06-18-wb-svc-048-implement-topic-discovery-job-and-scheduler.md`
- `.agent/tasks/current-task.md`
- `app/Console/Commands/DiscoverContentTopicsCommand.php`
- `app/Jobs/AI/DiscoverContentTopicsJob.php`
- `app/Modules/Ai/Data/DiscoverContentTopicsData.php`
- `app/Modules/Ai/Services/RunTopicDiscoveryService.php`
- `bootstrap/app.php`
- `routes/console.php`
- `tests/Feature/TopicDiscoveryExecutionTest.php`

## Validation

- `php -l app/Modules/Ai/Services/RunTopicDiscoveryService.php` ✅
- `php -l app/Jobs/AI/DiscoverContentTopicsJob.php` ✅
- `php -l app/Console/Commands/DiscoverContentTopicsCommand.php` ✅
- `php -l tests/Feature/TopicDiscoveryExecutionTest.php` ✅
- `php artisan test tests/Feature/TopicDiscoveryExecutionTest.php` ✅
- `php artisan schedule:list` ✅
- `php artisan test tests/Feature/AiContentAgentContractsTest.php tests/Feature/TopicDiscoveryAgentTest.php tests/Feature/TopicDiscoveryExecutionTest.php` ✅
- `php artisan test` ⚠️ failed in existing `Tests\Feature\ActivityLogTest::test_editorial_mutations_are_recorded_with_curated_audit_payloads` because media storage attempted to fetch instance-profile credentials from `169.254.169.254`

## Risks Or Follow-Ups

- scheduler cadence is currently a simple daily sweep across all approved clusters and may need product tuning later
- full-suite validation remains blocked by the unrelated activity-log/media credential failure noted above

## Completion Notes

- topic discovery can now be run via queued job, scheduled command, or direct CLI execution, all through the same service-layer orchestration path
