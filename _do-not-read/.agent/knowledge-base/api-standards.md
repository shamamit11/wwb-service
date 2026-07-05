# API Standards

## Purpose

Use this file for stable service API conventions in `widewebblog/service`.

## API Style

- APIs are admin-oriented and power internal consumers first.
- Prefer predictable REST-style resource endpoints.
- Keep response shapes stable and explicit.
- Version only when breaking external contracts cannot be avoided.

## Response Conventions

- Success list responses should include `data`, `meta`, and `links` when paginated.
- Success item responses should include `data`.
- Validation errors should use Laravel validation payloads unless the project adopts a custom envelope.
- Domain or business-rule failures should map to consistent error codes and human-readable messages.

## Resource Rules

- Use API Resources for output formatting.
- Do not return Eloquent models directly from controllers.
- Hide internal storage, queue, and provider implementation details unless required by the contract.
- Include relationships only when requested or already loaded.

## Request Conventions

- Validate with FormRequest classes.
- Convert validated payloads into DTOs before business logic.
- Normalize booleans, enums, dates, and arrays before they reach services.

## Filtering And Sorting

- Support explicit query parameters only.
- Keep filter names aligned to database-neutral business concepts.
- Prefer `sort=field` and `sort=-field` for ascending and descending order.
- Default sorting should be deterministic.

## Pagination

- Default to cursor or length-aware pagination according to the repository standard.
- Keep page size bounded.
- Always return pagination metadata for list endpoints.

## Idempotency And Safety

- `GET` endpoints must not mutate state.
- `POST` creates or triggers work.
- `PUT` or `PATCH` update.
- `DELETE` should be soft-delete aware where recovery matters.

## Auth And Authorization

- Admin-only endpoints should require authenticated admin context.
- Authorization rules belong in policies, gates, or dedicated guards, not controllers.

## Documentation

- Keep Scramble examples aligned with API Resources and FormRequests.
- When contract fields change, update the resource, validation, and docs together.
