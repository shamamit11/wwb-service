# Queue Conventions

## Purpose

Use this file for stable queue and scheduler rules in the service repository.

## Queue Use Cases

- AI generation and analysis jobs
- media processing
- sitemap or feed refresh tasks
- content scoring or audit tasks
- sync or cleanup jobs

## Job Design Rules

- Jobs should do one business operation well.
- Pass IDs or small DTO payloads, not heavy model graphs.
- Re-hydrate models inside the job.
- Make retries safe through idempotent business logic where possible.

## Queue Separation

- Use separate queues when workloads have different urgency or runtime profiles.
- Keep user-facing admin actions off long-running AI queues.
- Reserve low-priority queues for refresh, audit, and maintenance work.

## Retry Strategy

- Use explicit retry counts and backoff for provider or network failures.
- Do not retry validation or permanent business-rule failures.
- Capture provider context needed for debugging before failing hard.

## Failure Handling

- Log actionable failure metadata.
- Persist job status for long-running or user-visible workflows.
- Surface failed AI and media jobs in a trackable domain table instead of relying only on queue internals.

## Scheduler Rules

- Scheduled commands should orchestrate discovery or dispatch, not contain large business workflows.
- Scheduler entries must be safe to run more than once.
- Prefer short, composable commands over large daily catch-all commands.

## Operational Guidance

- Name queues clearly by workload.
- Track runtime hotspots and provider cost hotspots separately.
- Add tests for dispatch conditions, not just the command shell.
