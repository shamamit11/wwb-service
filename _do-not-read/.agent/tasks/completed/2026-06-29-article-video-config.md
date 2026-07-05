# Task Summary

Implement `AV-SVC-2.1` for Article Videos by adding service configuration and environment wiring for recommendation, draft generation, TTS, render mode, and storage defaults.

## Requested Outcome

- add Article Videos config
- support weekly limit, lookback days, and render mode
- support script model, TTS model, and TTS voice defaults without scattering env reads
- validate the config contract with focused automated coverage

## Scope Boundaries

- in scope: config files, `.env.example`, config contract tests
- out of scope: jobs, services, commands, API endpoints, Admin UI

## Assumptions

- a dedicated `config/article_videos.php` file is the cleanest place for feature-owned settings
- OpenAI text-model defaults already belong in `config/ai.php`, so Article Videos should reference that config pattern rather than inventing a separate provider config structure
- TTS-specific defaults were not previously exposed in `config/ai.php`, so adding them there is within scope for this task

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/media-service.md`
- `.agent/TESTING.md`
- `.agent/COMMANDS.md`

## Repository Files Inspected

- `config/ai.php`
- `config/newsletter.php`
- `config/filesystems.php`
- `config/services.php`
- `.env.example`
- `tests/Feature/LaravelAiClientTest.php`

## Plan

1. Add a dedicated Article Videos config file with feature-owned defaults.
2. Extend AI config only where Article Videos needs missing audio/TTS defaults.
3. Update `.env.example` with the new environment keys.
4. Add a focused config test covering fallback behavior and env-backed values.
5. Run targeted validation and formatting.

## Changed Files

- `.agent/tasks/current-task.md`
- `.env.example`
- `config/ai.php`
- `config/article_videos.php`
- `tests/Feature/ArticleVideoConfigTest.php`

## Validation

- `php -l config/article_videos.php && php -l config/ai.php && php -l tests/Feature/ArticleVideoConfigTest.php`
- `DB_CONNECTION=sqlite DB_DATABASE=/Users/amitsharma/Herd/widewebblog/service/database/database.sqlite php artisan test tests/Feature/ArticleVideoConfigTest.php`
- `vendor/bin/pint tests/Feature/ArticleVideoConfigTest.php`

## Risks Or Follow-Ups

- FFmpeg binary path and runtime-specific temp-directory defaults may need adjustment once the actual render service is implemented on the target hosting environment
- `article_videos.ai.script_model` and TTS values currently resolve from env-backed defaults; if multiple providers are added later, a stronger provider-specific resolver may be worth centralizing in a service

## Completion Notes

- added `config/article_videos.php` with feature-owned settings for limits, queues, render defaults, storage, and AI/TTS values
- extended `config/ai.php` to expose OpenAI audio model and voice defaults for Article Video usage
- updated `.env.example` with Article Video and OpenAI audio/TTS environment keys
- added focused coverage for the Article Video config contract and AI audio defaults
