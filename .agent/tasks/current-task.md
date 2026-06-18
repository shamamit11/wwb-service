# Task Record

## Task Summary

Implement `WB-SVC-060` by adding explicit editorial generation modes for AI draft creation.

## Requested Outcome

- add a mode-aware draft generation contract for supported article styles
- expose the mode selection through admin API and MCP
- keep the default generation path backward compatible when no mode is provided
- ensure mode selection changes prompt/template behavior without changing draft-only review rules

## Scope Boundaries

- in scope: service-side workflow, DTOs, validation, API route updates if needed, MCP updates if needed, AI workflow plumbing, and tests
- out of scope: admin UI implementation, title/excerpt refinement tooling, publishing behavior, and newsletter work

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`

## Repository Files Inspected

- `PHASE2.md`
- `WB_SERVICE_AI_AGENTS_TASKS.md`

## Plan

1. Inspect the existing blog draft generation DTOs, workflow, and prompt rendering path to determine the cleanest place for mode-aware generation.
2. Add the generation-mode contract, workflow plumbing, and test coverage while preserving the default draft path.
3. Validate with focused tests and full `php artisan test`, then archive/commit/push.

## Changed Files

- `.agent/tasks/current-task.md`

## Validation

- not run yet

## Risks Or Follow-Ups

- generation modes should stay prompt/template driven; if the service starts branching into deeply different persistence rules, that should be a later design decision

## Completion Notes

- implementation not started yet
