# Scaffold Map

This repository does not use the template's nested `app/Modules/<Module>/Http/...` layout.
Follow the actual structure already present here.

## Backend Flow

`Controller -> FormRequest -> Data DTO -> Service/Workflow -> Repository -> Resource`

## Preferred Locations

- `app/Http/Controllers/Api/...`: transport-only controllers
- `app/Http/Requests/Api/...`: validation and request normalization
- `app/Http/Resources/Api/...`: API response shaping
- `app/Modules/<Domain>/Data`: immutable DTOs and command data
- `app/Modules/<Domain>/Services`: use-case orchestration
- `app/Modules/<Domain>/Repositories`: persistence and query boundaries
- `app/AI/...`: agent contracts, DTOs, tools, and agent implementations
- `app/Mcp/...`: MCP tools, prompts, resources, and server registration
- `tests/Feature`: request, workflow, and integration behavior
- `tests/Unit`: focused unit coverage

## Existing Conventions To Preserve

- Shared helpers belong in `app/Support` or other existing cross-cutting namespaces only when they are truly reusable.
- New backend features should generally extend an existing domain module before introducing a new one.
- AI automation work should fit the current split between `app/AI`, `app/Modules/Ai`, and `app/Mcp`.
