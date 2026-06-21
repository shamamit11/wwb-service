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

Current practical baseline:

- use `php artisan migrate:fresh --seed` for schema-level validation
- use targeted Pest runs before full suite runs
- use `php artisan test` for final repository validation when the change crosses modules
- prefer verifying the simplified article-first pipeline over preserving removed brief/template/block behavior
