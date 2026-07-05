# Coding Rules

- Follow the existing Laravel patterns already used in this repository.
- Keep controllers transport-only.
- Use `FormRequest` classes for validation and authorization.
- Move structured use-case input into immutable `Data` DTOs under `app/Modules/<Domain>/Data` when the flow is non-trivial.
- Keep business behavior in `Service`, `Workflow`, or agent classes, not in controllers or requests.
- Keep persistence and query behavior in repository classes where the project already uses that pattern.
- Return API resources instead of raw Eloquent models from API controllers.
- Reuse existing module boundaries before adding new top-level folders.
- Add or update tests for behavior changes.
- Update local agent docs when repository conventions materially change.
