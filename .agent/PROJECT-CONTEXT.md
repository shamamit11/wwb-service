# Project Context

## Product Context

Wide Web Blog is a blogging platform where the MVP starts with an admin user who can create and publish blog posts.

The current service roadmap now includes a concrete Phase 3 AI content engine for:

- topic discovery inside approved niche clusters
- content brief generation from approved topics
- draft post generation from approved briefs
- human-reviewed AI-assisted editorial workflows

Later phases may expand into richer SEO automation, image workflows, and external AI client integrations.

## Repository Scope

This repository is `widewebblog/service`.

It is responsible for the Laravel backend/service only.

Sibling apps:

- `../admin`
- `../fe`

are separate applications.

The service agent must not scan or modify sibling apps unless the task explicitly requires it.

## Service Responsibilities

The Laravel service is responsible for:

- API endpoints
- authentication and authorization
- blog post management
- category management
- tag management if implemented
- CMS-related backend data
- AI content generation pipeline
- AI prompt template management and versioning
- AI job, generation-step, token, and cost tracking
- topic and content brief workflow management
- SEO metadata
- slugs
- media and image storage
- database schema and migrations
- business rules
- validation
- API resources and responses
- background jobs and events when needed
- integration with external AI, image, and storage services

## MVP Scope

For MVP:

- Admin can create, update, publish, unpublish, and delete blog posts.
- Admin can manage blog categories.
- Blog posts should support SEO fields.
- Blog posts should support featured images or media.
- AI workflows are service-driven and draft-first.
- Public frontend will consume published content from the service.

### AI Content Engine MVP

- Knowledge Base entries can ground AI workflows.
- Topic discovery creates suggested topics only.
- Only approved topics can generate content briefs.
- Only approved content briefs can generate draft posts.
- All AI-generated posts remain `draft` until manual admin approval.
- Images are manual in this phase. AI may suggest image ideas, placement notes, and alt text only.
- AI prompts must be database-backed and versioned, not hardcoded in agents.
- AI workflows must be auditable through job and step tracking.

### AI Content Clusters

Topic discovery must stay within these clusters unless a later task expands them:

- `ai_tools`
- `ai_for_blogging`
- `seo`
- `content_marketing`
- `productivity_automation`
- `developer_ai`

## Non-Goals For Service Agent

The service agent should not implement:

- admin UI screens unless explicitly asked
- public frontend UI unless explicitly asked
- whole-repository refactors
- new architecture that conflicts with documented patterns
- unrequested package or library changes

## Technology Baseline

- Laravel 13 for backend application logic and APIs
- Eloquent models and migrations for persistence
- Cloudflare R2 for media storage
- future AI and image providers behind service-side abstractions

## Product Constraints

- AI-generated content must never publish directly.
- AI-generated drafts require explicit admin review and approval.
- Only approved upstream entities may move forward in the AI pipeline.
- Retry logic must not duplicate topics, briefs, or posts.
- Long-running AI work should use the explicit `ai` queue.
- Public frontend should consume only published content states.
- Agent work should remain task-driven and based on minimal context loading.

## Working Assumption

This `.agent/` documentation was created before concrete Laravel source files were present in this workspace. Update command and architecture details when the actual service repository files become available, but preserve the documented service boundaries.
