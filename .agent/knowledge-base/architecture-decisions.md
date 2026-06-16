# Architecture Decisions

## Initial Decisions

- Keep agent documentation centralized under `.agent/`.
- Use `.agent/INDEX.md` as the single entrypoint for all coding agents.
- Keep root-level agent files as redirect-only files to reduce duplicated instructions.
- Optimize for task-based context loading instead of repository-wide scanning.
- Separate stable memory, active task state, and incomplete-work handover into different files.

## Pending Decisions

Document these once implementation details are confirmed:
- concrete repository boundaries for `service/`, `admin/`, and `fe/`
- queue and job orchestration patterns
- AI provider integration boundaries
- exact testing and CI strategy
