# Current Task

## Task Summary

Configure the service infrastructure baseline for MySQL, Redis-backed cache and queues, scheduler wiring, and storage disk scaffolding.

## Requested Outcome

- wire MySQL as the service database baseline
- wire Redis as the cache and queue baseline
- add a scheduler baseline that can support future SEO and AI orchestration jobs
- add storage disk scaffolding for local/public media and future object storage

## Scope Boundaries

- in scope: `.env.example`, infrastructure-related `config/*.php`, `routes/console.php`, task tracking, runtime validation
- out of scope: feature-specific jobs, UI changes, sibling repositories

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/skills/queue-scheduler.md`

## Repository Files Inspected

- `.env`
- `.env.example`
- `config/database.php`
- `config/cache.php`
- `config/queue.php`
- `config/filesystems.php`
- `routes/console.php`
- `bootstrap/app.php`
- `composer.json`

## Plan

1. Tighten the infrastructure defaults toward MySQL, Redis, and explicit storage disks.
2. Add a minimal scheduler baseline using safe operational commands.
3. Validate MySQL migrations, one-shot queue worker startup, and scheduler listing against the local runtime.
4. Record changed files, validation results, and residual risk.

## Changed Files

- `.agent/tasks/current-task.md`
- `.env.example`
- `config/cache.php`
- `config/database.php`
- `config/filesystems.php`
- `config/queue.php`
- `routes/console.php`

## Validation

- `php artisan migrate --force`
- `php artisan migrate:status`
- `php artisan queue:work --once`
- `php artisan schedule:list`

## Risks Or Follow-Ups

- The storage scaffolding defines local `media` and object-storage `r2` disks, but no feature code consumes `filesystems.media_disk` yet.

## Completion Notes

- Summary: Switched the infrastructure baseline to MySQL and Redis, added explicit media and R2 disk scaffolding, and introduced safe scheduled maintenance commands as the initial scheduler baseline.
- Changed files: Updated `.env.example`, `config/database.php`, `config/cache.php`, `config/queue.php`, `config/filesystems.php`, and `routes/console.php`.
- Validation run: Created the configured MySQL database if missing, ran `php artisan migrate --force`, confirmed `php artisan migrate:status`, confirmed `php artisan queue:work --once` exits cleanly against Redis with `REDIS_QUEUE_BLOCK_FOR=1`, and confirmed `php artisan schedule:list` shows the scheduled maintenance commands.
- Risks: The local `.env` was aligned for runtime validation using the existing MySQL host `127.0.0.1:6666`; other environments still need their own concrete secrets and endpoints.
- Follow-ups: When AI and SEO workflows land, add thin orchestration commands to the scheduler that dispatch jobs onto dedicated Redis queues rather than embedding heavy logic directly in scheduled closures.
