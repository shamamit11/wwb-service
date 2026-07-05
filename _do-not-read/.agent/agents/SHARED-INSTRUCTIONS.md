# Shared Agent Instructions

## Operating Goal

Complete tasks with the minimum necessary context while keeping behavior consistent across Claude, Codex, and GitHub Copilot.

## Shared Rules

1. Start at `.agent/INDEX.md`.
2. Read `.agent/tasks/current-task.md` before exploring code.
3. Do not scan the whole repository unless the task requires it.
4. Prefer targeted file reads over directory-wide discovery.
5. Update the current task file throughout the task.
6. Update `.agent/MEMORY.md` only for stable reusable knowledge.
7. Update `.agent/AGENT-HANDOVER.md` when pausing incomplete work.
8. Preserve the product rule that AI-generated content stays draft until admin approval.

## Context Loading Order

1. Current task file
2. Shared instructions
3. Agent-specific file
4. Only the relevant project context, skill, testing, and knowledge files
5. Only then, the smallest necessary repository files

## Behavior Standard

- Be explicit about scope.
- Keep notes concise and factual.
- Prefer task progress tracking over long free-form reasoning.
- Record validation and residual risk.
