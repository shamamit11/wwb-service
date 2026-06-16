# Service Agent Index

This is the only file an agent should read first.

## Repository Scope

This `.agent` directory belongs to the `widewebblog/service` repository.

The service agent is responsible for Laravel backend/API/service work.

Sibling applications exist at:

- `../admin`
- `../fe`

Do not inspect sibling applications by default.
Only reference them when the active task explicitly requires cross-app context.

## Purpose

Use this directory to keep agent context small, task-driven, and reusable across Claude, Codex, and GitHub Copilot for service-repository work.

## Core Rules

1. Work from `widewebblog/service` unless instructed otherwise.
2. Do not scan the whole repository by default.
3. Load only the minimum files needed for the active task.
4. Do not scan `../admin` or `../fe` by default.
5. If cross-app context is needed, read lightweight sibling docs first.
6. Record why sibling context was needed in `.agent/tasks/current-task.md`.
7. Do not change sibling app files unless the task explicitly requests it.
8. Every task must create or update `.agent/tasks/current-task.md`.
9. After finishing a task, record changed files, validation, risks, and completion notes.
10. Move completed task notes into `.agent/tasks/completed/`.
11. Update `.agent/MEMORY.md` only for stable knowledge that will help future tasks.
12. Update `.agent/AGENT-HANDOVER.md` when work is incomplete or another agent may continue.

## Fast Start

1. Read `.agent/tasks/current-task.md`.
2. Read `.agent/agents/SHARED-INSTRUCTIONS.md`.
3. Read the agent-specific file in `.agent/agents/`.
4. Load only the service context required by the active task.
5. Load sibling app context only when the task explicitly requires it.

## Task-Based Context Loading

For Laravel API or backend feature work:
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/database.md`

For database, migrations, models, or repositories:
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/skills/database.md`

For AI content generation pipeline work:
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/product.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-content-engine.md`

For SEO metadata, slugs, sitemap, or structured data:
- `.agent/PROJECT-CONTEXT.md`
- `.agent/knowledge-base/seo.md`
- `.agent/skills/seo.md`

For media, image, or storage pipeline work:
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/skills/media-service.md`

For admin-impacting API changes:
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/skills/laravel-api.md`

Then, only if available and necessary:
- `../admin/.agent/MEMORY.md`
- `../admin/.agent/ARCHITECTURE.md`
- `../admin/.agent/API-CONTRACT.md`

Do not scan the full `../admin` project unless explicitly required by the task.

For public frontend-impacting API changes:
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/seo.md`

Then, only if available and necessary:
- `../fe/.agent/MEMORY.md`
- `../fe/.agent/ARCHITECTURE.md`
- `../fe/.agent/API-CONTRACT.md`

Do not scan the full `../fe` project unless explicitly required by the task.

For testing, debugging, or release validation:
- `.agent/TESTING.md`
- `.agent/COMMANDS.md`
- `.agent/skills/testing.md`

For architecture or cross-cutting service changes:
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/architecture-decisions.md`
- `.agent/MEMORY.md`

## Cross-Repository References

The service repository may affect `admin` and `fe`, but those apps are not owned by this agent by default.

Use sibling context only when the task involves:

- API contract changes
- request or response payload changes
- authentication or authorization behavior
- CMS data consumed by admin or frontend
- media or image URL behavior
- SEO metadata consumed by frontend
- breaking changes that require coordination

When sibling context is needed:

1. Prefer `.agent` docs in the sibling app.
2. Read only the smallest relevant file.
3. Do not scan the whole sibling repository.
4. Do not edit sibling files unless explicitly requested.
5. Record the reason in `.agent/tasks/current-task.md`.

## File Map

- `.agent/PROJECT-CONTEXT.md`: service product scope, backend stack, constraints, bounded assumptions
- `.agent/ARCHITECTURE.md`: Laravel service architecture, integrations, boundaries
- `.agent/COMMANDS.md`: service dev and validation commands
- `.agent/TESTING.md`: service validation strategy and minimum test expectations
- `.agent/TASK-WORKFLOW.md`: how tasks are opened, updated, completed, and archived
- `.agent/AGENT-HANDOVER.md`: active handover state for unfinished work
- `.agent/MEMORY.md`: stable reusable service knowledge
- `.agent/agents/`: shared and agent-specific operating instructions
- `.agent/skills/laravel-api.md`: primary backend/API implementation guidance
- `.agent/skills/database.md`: migrations, models, repositories, and query guidance
- `.agent/skills/livewire-admin.md`: service-local or cross-reference admin guidance only when relevant
- `.agent/skills/livewire-frontend.md`: service-local or cross-reference frontend guidance only when relevant
- `.agent/skills/media-service.md`: service-side media storage and retrieval guidance
- `.agent/skills/seo.md`: SEO-related service and metadata guidance
- `.agent/skills/testing.md`: validation scope and testing discipline
- `.agent/tasks/`: current task plus template and completed task records
- `.agent/knowledge-base/`: durable product and domain references

## When To Read More

Read additional repository files only when:
- the current task file references them
- the requested change touches them directly
- validation requires them
- the current docs are insufficient or stale

If deeper exploration becomes necessary, record why in `.agent/tasks/current-task.md`.
