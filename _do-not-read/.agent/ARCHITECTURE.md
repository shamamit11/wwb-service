# Architecture

## Service Scope

This document describes the current Laravel backend architecture for `widewebblog/service`.

The service owns:

- API contracts
- business logic
- persistence
- media handling
- SEO data
- AI content workflows
- prompt template management
- integrations

It does not own `../admin` or `../fe` by default.

## Default Layering

Use this pattern unless an existing module already establishes a narrower local pattern:

`Controller -> FormRequest -> DTO -> Service/Workflow -> Repository -> Model/External Client -> API Resource`

For AI workflows:

`Controller/Command -> FormRequest/Console Input -> DTO -> Workflow Service -> AI Client + Prompt Renderer + Internal Tools -> Repository -> Job Tracking -> API Resource`

## Layer Rules

### Controllers

Controllers stay thin. They may:

- accept requests
- validate via FormRequests
- turn validated input into DTOs
- call services or workflows
- return resources or JSON responses

Controllers should not contain business logic, storage orchestration, or AI prompting logic.

### Form Requests

FormRequests handle validation and lightweight normalization only.

### DTOs

DTOs are readonly or effectively immutable payload objects. They should not query the database or receive injected services.

Typical examples:

- `CreatePostData`
- `UpdatePostData`
- `DiscoverContentTopicsData`
- `QueueBlogDraftGenerationData`
- `UploadMediaData`

### Services And Workflows

Services own business rules. Workflow services own multi-step orchestration, retries, and job dispatch.

Typical examples:

- `CreatePostService`
- `UpdatePostService`
- `PublishPostService`
- `TopicDiscoveryWorkflow`
- `DraftGenerationWorkflow`

### Repositories

Repositories encapsulate Eloquent query and persistence logic. They should not own business workflows.

### Models

Models define relationships, casts, scopes, and lightweight attribute behavior. Keep larger workflows outside the model.

### API Resources

Resources format responses and should remain independent from service logic.

### Jobs

Jobs are used for slow or retryable work. AI jobs should use the explicit `ai` queue and must be safe to retry without duplicating topics or posts.

### External Clients

External APIs must stay behind abstractions. AI providers are accessed through internal AI client abstractions so agents remain provider-agnostic.

## AI Workflow Architecture

The current AI content engine is a controlled editorial pipeline.

Core stages:

1. Knowledge Base context is selected and formatted.
2. `TopicDiscoveryAgent` suggests scored topics inside approved clusters.
3. low-scoring topics are pruned automatically.
4. high-scoring topics queue `BlogWriterAgent` automatically.
5. generated posts remain `draft` for admin review and manual publishing.

Supporting components:

- database-backed prompt templates and versions
- AI workflow orchestration services
- internal AI tools for duplicate checks, persistence, post search, and internal link lookup
- `ai_jobs` tracking
- `ai_generation_steps` tracking
- token and cost recording when available

Non-negotiable rules:

- prompts for the main topic/blog flow must be versioned in prompt templates
- controllers dispatch workflows rather than orchestrating steps directly
- every meaningful AI workflow creates an `ai_jobs` record
- every agent execution creates an `ai_generation_steps` record
- failures must be visible and retryable without creating duplicate domain records
- AI never publishes directly

## Content Architecture

Posts are article-first.

Canonical content fields:

- `full_article_markdown`
- optional `full_article_html`
- `short_description`
- `description`
- `faq`

Templates, content briefs, and block collections are not part of the active service architecture.

## Transactions

Use database transactions when a use case writes multiple related records or changes state across multiple tables.

## Response Standard

- use API Resources for model responses
- keep error payloads consistent
- keep services response-agnostic
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
