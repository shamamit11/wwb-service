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

## Repository Files Inspected

- `PHASE2.md`
- `WB_SERVICE_AI_AGENTS_TASKS.md`

## Plan

1. Inspect the existing SEO metadata, post, and AI workflow services to find the cleanest place for metadata suggestion orchestration.
2. Add the metadata suggestion workflow, queue path, admin API contract, MCP surface, and tests.
3. Validate with focused tests and full `php artisan test`, then archive/commit/push.

## Changed Files

- `.agent/tasks/current-task.md`

## Validation

- not run yet

## Risks Or Follow-Ups

- metadata suggestions should remain review-only by default; auto-applying them would need a separate editorial action

## Completion Notes

- implementation not started yet
