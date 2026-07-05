# Task Summary

Implement `AV-SVC-2.2` for Article Videos by registering queue separation, retry policy, and worker guidance for upcoming recommendation, draft, and render jobs.

## Requested Outcome

- formalize Article Video queue names and retry behavior
- add explicit retry and timeout config for AI-provider and render workloads
- document the required worker queues for local and production execution
- validate the config and command guidance with focused coverage

## Scope Boundaries

- in scope: config updates, `.env.example`, command guidance, focused config/documentation tests
- out of scope: actual job classes, workflow dispatch changes, API endpoints, Admin UI

## Assumptions

- Article Video jobs are not implemented yet, so this task establishes the config contract they will consume
- `ai` remains the correct queue for recommendation and draft generation, while `video-render` remains the separate queue for TTS and FFmpeg workloads
- local and production worker guidance belongs in `.agent/COMMANDS.md` because that is the repo’s documented execution reference

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/skills/queue-scheduler.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`

## Repository Files Inspected

- `config/queue.php`
- `config/article_videos.php`
- `app/Jobs/AI/DiscoverContentTopicsJob.php`
- `app/Jobs/AI/GenerateBlogDraftJob.php`
- `app/Jobs/News/DiscoverNewsItemsJob.php`
- `tests/Feature/RunWeeklyContentPlanCommandTest.php`
- `tests/Feature/TopicDiscoveryWorkflowDailyLimitTest.php`

## Plan

1. Extend Article Video config with explicit retry, backoff, and timeout policy for AI and render workloads.
2. Update `.env.example` with any new queue/retry keys needed by the config contract.
3. Document the required worker queues in `.agent/COMMANDS.md`.
4. Add focused test coverage for the new config and command guidance.
5. Run targeted validation and formatting.

## Changed Files

- `.agent/tasks/current-task.md`
- `.agent/COMMANDS.md`
- `.env.example`
- `config/article_videos.php`
- `tests/Feature/ArticleVideoConfigTest.php`

## Validation

- `php -l config/article_videos.php && php -l tests/Feature/ArticleVideoConfigTest.php`
- `DB_CONNECTION=sqlite DB_DATABASE=/Users/amitsharma/Herd/widewebblog/service/database/database.sqlite php artisan test tests/Feature/ArticleVideoConfigTest.php`
- `vendor/bin/pint tests/Feature/ArticleVideoConfigTest.php`

## Risks Or Follow-Ups

- once the actual Article Video jobs are added, they still need to read and apply this config explicitly
- production deployment still needs worker process changes outside the repository so `video-render` is actually consumed

## Completion Notes

- extended `config/article_videos.php` with explicit per-job queue, tries, backoff, and timeout settings for recommendation, draft, and render workloads
- updated `.env.example` with Article Video queue and retry environment keys
- documented `video-render` and `ai` worker expectations in `.agent/COMMANDS.md`
- expanded `ArticleVideoConfigTest` to cover retry policy and worker guidance
