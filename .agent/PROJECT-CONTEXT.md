# Project Context

## Product Context

Wide Web Blog is a blogging platform where the MVP starts with an admin user who can create and publish blog posts.

The platform will later support AI-assisted content generation, AI-generated or AI-assisted images, SEO optimization, media handling, and public frontend rendering.

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
- AI-generated content may be introduced behind service-level abstractions.
- Public frontend will consume published content from the service.

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
- Public frontend should consume only published content states.
- Agent work should remain task-driven and based on minimal context loading.

## Working Assumption

This `.agent/` documentation was created before concrete Laravel source files were present in this workspace. Update command and architecture details when the actual service repository files become available, but preserve the documented service boundaries.
