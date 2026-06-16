# Service Module Conventions

New backend features should follow this default flow:

`Controller -> FormRequest -> DTO -> Service/Action -> Repository -> Model/External Client -> API Resource`

## Directory Intent

- `app/Http/Controllers/Api/...`: transport-only controllers
- `app/Http/Requests/Api/...`: validation and request normalization
- `app/Modules/<Domain>/Data`: readonly DTOs built from validated input
- `app/Modules/<Domain>/Services`: use-case orchestration
- `app/Modules/<Domain>/Repositories`: query and persistence logic
- `app/Http/Resources/Api/...`: response formatting
- `app/Support/...`: shared cross-cutting helpers only

## Rules

- Controllers stay thin and should not contain business logic.
- Requests validate and authorize input only.
- DTOs should be immutable and free of I/O.
- Services coordinate workflows and transactions when needed.
- Repositories own Eloquent queries and persistence details.
- Resources define API payloads and never leak internal fields.
