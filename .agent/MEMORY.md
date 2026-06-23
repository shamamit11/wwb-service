# Agent Memory

This memory belongs to `widewebblog/service`.

Store only stable, reusable service knowledge here. Do not write temporary task notes, speculative ideas, or one-off debugging details.

## Stable Project Knowledge

- This repository is the Laravel 13 backend and service for Wide Web Blog.
- Sibling apps exist at `../admin` and `../fe`, but they are not owned by the service agent.
- The service owns API contracts, business logic, persistence, media handling, SEO data, AI content workflows, and integrations.
- Service work should remain task-driven and avoid broad repository scanning.
- The service follows a layered Laravel architecture:
  `Controller -> FormRequest -> DTO -> Service/Action -> Repository -> Model/External Client -> API Resource`.
- This layered pattern is the default for new backend features unless an existing module already has an established local pattern or the task explicitly instructs otherwise.
- Controllers should stay thin.
- Validation belongs in FormRequests.
- Business logic belongs in services or actions.
- Query logic belongs in repositories.
- Response formatting belongs in API Resources.
- External API calls should be isolated behind client classes.
- AI content and image generation should be implemented behind service and client abstractions, not directly inside controllers.
- The editorial AI flow is topic discovery -> topic scoring -> automatic pruning below 70 -> editorial review for 70-84.99 -> automatic draft generation for scores at or above 85 -> human review -> manual publish.
- AI-generated content must remain in draft until explicit admin approval.
- Posts are article-first records rather than block-based compositions.
- The system no longer uses content briefs, templates, template blocks, or post content blocks.
- Prompt management is database-backed and versioned, but limited to the standard prompt families used by the main flow: topic generation and blog generation.
- Site settings should not duplicate active topic/blog prompt instruction fields when versioned prompt templates are the source of truth.
- Images remain manual in the current AI phase; AI may only provide image ideas, placement notes, and alt text suggestions.
- Queue and cache defaults are database-backed; Redis is not a required baseline dependency.
- SEO fields and published content should be designed for consumption by the public frontend.

## Update Policy

Add entries only when all are true:

- the knowledge is likely to remain true across multiple tasks
- it reduces repeated context loading
- it affects implementation or validation decisions

Do not add:

- temporary task details
- unresolved questions
- meeting notes
- speculative architecture
- partial implementation details likely to change soon
