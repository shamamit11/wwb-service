# Skill: Queue And Scheduler

## Purpose

Use this skill for queued jobs, scheduled commands, async workflows, retries, and workload separation.

## Context To Load

- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/content-lifecycle.md` when content states are involved
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/skills/testing.md`

## Use This When

- dispatching background jobs
- adding scheduled discovery or refresh tasks
- splitting workloads across queues
- defining retry and failure rules

## Design Checklist

- define job responsibility clearly
- pass minimal payloads
- re-hydrate models inside the job
- choose queue name intentionally
- define retries and backoff intentionally
- persist user-visible workflow status where needed
- test dispatch conditions and failure boundaries

## Scheduler Rules

- scheduler triggers orchestration only
- heavy logic belongs in services, actions, or jobs
- scheduled work must be safe to rerun
