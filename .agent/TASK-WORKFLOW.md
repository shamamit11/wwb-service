# Task Workflow

This workflow is for service work inside `widewebblog/service`.

## Starting A Task

1. Read `.agent/INDEX.md`.
2. Read `.agent/tasks/current-task.md`.
3. Read `.agent/agents/SHARED-INSTRUCTIONS.md`.
4. Read only the skill and context files required by the task.
5. Inspect only the service files directly related to the task.

## During A Task

The agent must:

- keep changes small
- follow the documented Laravel 13 pattern
- avoid broad refactors
- avoid scanning unrelated files
- avoid editing sibling apps
- update `.agent/tasks/current-task.md` with assumptions, changed files, validation, and risks
- mark uncertain assumptions as `TBC`

## Required Current Task Fields

- task summary
- requested outcome
- scope boundaries
- context files loaded
- repository files inspected
- plan
- changed files
- validation
- risks or follow-ups
- completion notes

## Cross-App Context

If the task requires checking `../admin` or `../fe`, record:

```md
## Cross-App Context Used

- App checked:
- Files/docs read:
- Reason:
- Impact on service:
```

Do not read or edit sibling app files unless the task explicitly requires it.

## Completing A Task

At completion, update `.agent/tasks/current-task.md` with:

```md
## Completion Notes

- Summary:
- Changed files:
- Validation run:
- Risks:
- Follow-ups:
```

Then move or copy the completed task note into `.agent/tasks/completed/` when that workflow is in use.

## Memory And Handover Discipline

- Put stable reusable knowledge in `.agent/MEMORY.md`.
- Put unfinished continuation details in `.agent/AGENT-HANDOVER.md`.
- Keep temporary notes and task-specific assumptions out of memory.
