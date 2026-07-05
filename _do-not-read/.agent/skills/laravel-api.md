# Skill: Laravel API

## Purpose

Use this skill for API endpoints, backend features, service logic, resources, requests, DTOs, repositories, and Laravel service implementation inside `widewebblog/service`.

## Required Pattern

All new backend features should follow:

`Controller -> FormRequest -> DTO -> Service/Action -> Repository -> Model/External Client -> API Resource`

## Context To Load

- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/skills/database.md` when persistence changes are involved

## Endpoint Implementation Checklist

For a new endpoint, consider:

- route
- controller method
- FormRequest
- DTO
- service or action
- repository method
- model relationships or casts if needed
- API Resource
- feature test
- authorization if needed
- OpenAPI or docs update if the repo has API docs

## Controller Rules

- thin only
- no business logic
- no direct external API calls
- no complex Eloquent queries
- no AI prompt building

## Service Rules

- own use case orchestration
- use transactions where needed
- call repositories and clients
- return domain result or model, not formatted response

## Repository Rules

- own persistence and query logic
- no request objects
- no response formatting
- no business workflow orchestration

## Resource Rules

- own response shape
- avoid leaking internal implementation fields
- use `whenLoaded` for relationships

## Error Handling

- use Laravel validation errors for invalid input
- use domain exceptions for business-rule failures
- convert exceptions to consistent API errors where the project already has a pattern
- do not swallow exceptions silently

## Authorization

- use policies, gates, middleware, or guards according to the existing project pattern
- do not invent a new auth pattern unless the task requires it
