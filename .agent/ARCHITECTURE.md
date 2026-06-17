# Architecture

## Service Scope

This document describes the expected Laravel 13 backend architecture for `widewebblog/service`.

The service owns:

- API contracts
- business logic
- persistence
- media handling
- SEO data
- AI content workflows
- integrations

It does not own the `../admin` or `../fe` applications by default.

## Design Pattern Decision

The service follows a layered Laravel architecture:

`Controller -> FormRequest -> DTO -> Service/Action -> Repository -> Model/External Client -> API Resource`

This is the default pattern for new backend features.

Reasons:

- Keeps controllers thin.
- Keeps validation separate.
- Keeps business logic testable.
- Keeps persistence logic reusable.
- Keeps API response formatting consistent.
- Makes AI coding-agent changes safer and smaller.

Agents must follow this pattern unless the existing module already uses a different established local pattern or the task explicitly instructs otherwise.

## Preferred Flow

For standard use cases:

`Controller -> FormRequest -> DTO -> Service/Action -> Repository -> Model/External Client -> API Resource`

For complex workflows:

`Controller -> FormRequest -> DTO -> Application Service -> Domain Service/Action -> Repository/External Client -> Event/Job -> API Resource`

For AI workflows:

`Controller/Command -> FormRequest/Console Input -> DTO -> Workflow Service -> AI Client + Prompt Renderer + Internal Tools -> Repository -> Job Tracking -> API Resource`

## Layer Rules

### Controllers

Controllers should be thin.

Controllers may:

- accept requests
- use FormRequest validation
- create DTOs from validated input
- call service or action classes
- return API resources or JSON responses

Controllers should not contain:

- business logic
- query-heavy logic
- AI prompt construction
- storage logic
- external API orchestration
- complex conditionals

### Form Requests

Use FormRequest classes for validation.

FormRequests should handle:

- input validation
- authorization when request-specific
- normalizing validated request data when appropriate

Avoid putting business logic in FormRequests.

### DTOs

Use DTOs to pass structured validated data into services.

DTOs should:

- be simple immutable or readonly objects where possible
- be created from validated request data
- avoid dependency injection
- avoid database queries

Suggested naming:

- `CreatePostData`
- `UpdatePostData`
- `GenerateContentData`
- `UploadMediaData`

### Services And Actions

Use service or action classes for business use cases.

Examples:

- `CreatePostService`
- `UpdatePostService`
- `PublishPostService`
- `GeneratePostContentService`
- `UploadMediaService`

Services may:

- coordinate repositories
- apply business rules
- dispatch jobs or events
- call external clients
- manage transactions where needed

Services should not directly format API responses.

### Repositories

Use repositories for persistence and query logic.

Repositories should:

- encapsulate Eloquent queries
- handle reusable query filters
- keep services clean
- return models, collections, paginators, or domain-specific results

Suggested names:

- `PostRepository`
- `CategoryRepository`
- `MediaRepository`

Do not place business workflows in repositories.

### Models

Models should represent database entities.

Models may include:

- relationships
- casts
- scopes
- accessors or mutators when useful
- fillable or guarded configuration

Avoid large business workflows inside models.

### API Resources

Use API Resource classes for response formatting.

Resources should:

- transform models into consistent API responses
- hide internal fields
- include relationships when loaded
- keep controllers and services response-agnostic

### Jobs And Events

Use jobs for slow, asynchronous, or retryable work.

Examples:

- generate AI content
- generate images
- process uploaded media
- build sitemap
- refresh SEO metadata

AI generation jobs should use the explicit `ai` queue and must be safe to retry without duplicating topics, briefs, or posts.

Use events when other parts of the service need to react to a domain change.

Examples:

- `PostPublished`
- `PostUnpublished`
- `MediaUploaded`
- `AiContentGenerated`

### External Clients

External API integrations should be isolated behind client classes.

Examples:

- `OpenAiContentClient`
- `ImageGenerationClient`
- `StorageClient`

Do not call external APIs directly from controllers.

For AI providers specifically:

- isolate provider SDK usage behind an internal AI client abstraction
- keep agents provider-agnostic
- capture raw response, parsed response, usage metadata, and error state in structured results

## AI Workflow Architecture

The Phase 3 AI content engine is a staged workflow, not a single generation endpoint.

Core stages:

1. Knowledge Base context is selected and formatted.
2. `TopicDiscoveryAgent` suggests topics inside approved clusters.
3. approved topics feed `ContentBriefAgent`.
4. approved briefs feed `BlogWriterAgent`.
5. generated posts remain `draft` for admin review and manual publishing.

Supporting components:

- database-backed prompt templates and versions
- AI workflow orchestration services
- internal AI tools for duplicate checks, persistence, post search, and internal link lookup
- `ai_jobs` lifecycle tracking
- `ai_generation_steps` per-agent execution tracking
- token and cost recording when usage metadata is available

Non-negotiable rules:

- prompts must not be hardcoded inside agent classes
- controllers should dispatch workflows, not contain orchestration logic
- every meaningful AI workflow must create an `ai_jobs` record
- every agent execution must create an `ai_generation_steps` record
- failures must be visible and retryable without creating duplicate domain records

### Transactions

Use database transactions in services when a use case writes multiple related records.

Example:

```php
DB::transaction(function () {
    // create post
    // attach categories
    // create SEO metadata
});
```

## Response Standard

Preferred response style:

- use Laravel API Resources for model responses
- use a consistent JSON shape for status and error responses
- use pagination resources for list endpoints
- use proper HTTP status codes

## Cross-Cutting Service Rules

- Controllers stay thin.
- Business logic belongs in services or actions.
- Query logic belongs in repositories.
- Validation belongs in FormRequests.
- Response formatting belongs in API Resources.
- External integrations belong behind client abstractions.
- AI content and image generation must remain draft-oriented until admin approval.
- AI workflow state must stay separate from editorial publish state.

## Cross-App Boundary

`../admin` and `../fe` may depend on this service, but they are separate apps.

Only inspect sibling apps when the active task requires API contract, payload, auth, media URL, SEO, or published-content coordination. Prefer their lightweight `.agent` docs first when available.
