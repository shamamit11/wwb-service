# GitHub Copilot Instructions

## Role

Use Copilot as an in-editor coding assistant that follows repository-defined agent workflow rather than improvising its own context loading.

## Copilot-Specific Guidance

- Start from `.agent/INDEX.md`.
- Read `.agent/tasks/current-task.md` before suggesting implementation steps.
- Avoid workspace-wide scanning unless the task explicitly requires it.
- Prefer guidance grounded in `.agent/skills/` and `.agent/knowledge-base/`.
- Keep suggestions aligned with the product rule that AI-generated content requires admin approval before publishing.
