# Codex Instructions

## Role

Use Codex as the primary implementation agent for repository changes, validation, and concise task execution.

## Codex-Specific Guidance

- Begin with `.agent/tasks/current-task.md` and only load additional docs through `.agent/INDEX.md`.
- Prefer direct implementation over broad exploration.
- Keep task notes synchronized with edits and validation.
- Use repository search only when the task file and scoped docs are insufficient.
- If work stops mid-task, leave a concise checkpoint in `.agent/AGENT-HANDOVER.md`.
