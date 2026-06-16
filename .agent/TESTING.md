# Testing

## Testing Goals

- Validate the requested change with the smallest reliable test scope.
- Prefer targeted checks before full-suite runs.
- Record what was run, what was not run, and why.

## Minimum Validation Expectations

For documentation-only changes:
- verify required files were created or updated
- verify cross-references point to existing paths
- verify root agent files only route to `.agent/INDEX.md`

For backend or API changes:
- run targeted Laravel tests when available
- run broader tests only if the change crosses multiple bounded contexts

For admin or frontend changes:
- run the most local UI or integration checks available
- confirm buildability when a change affects shared assets or layouts

For database changes:
- validate migration direction, rollback plan, and affected queries

## Reporting Format

Each task should capture:
- commands run
- result summary
- gaps not validated
- residual risk

## Current Baseline

This workspace did not include runnable application code when this scaffold was created, so no project-specific test matrix is documented yet.
