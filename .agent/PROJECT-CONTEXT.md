# Project Context

## Product Context

Wide Web Blog is a blogging platform with a simplified editorial backend centered on topic discovery, article drafting, manual review, and publishing.

The current backend direction is:

- knowledge-base-grounded topic discovery
- score-based topic routing
- automatic draft generation for high-scoring topics
- article-first post storage
- human-reviewed publishing

Later phases may expand admin editing UX, image workflows, and deeper SEO automation.

## Repository Scope

This repository is `widewebblog/service`.

It owns the Laravel backend only.

Sibling apps:

- `../admin`
- `../fe`

are separate applications and should not be scanned by default.

## Service Responsibilities

The service owns:

- API endpoints
- authentication and authorization
- posts, categories, tags, pages, media, and site settings
- topic queue and topic scoring workflow
- knowledge base support
- AI prompt template management and versioning
- AI job, generation-step, token, and cost tracking
- SEO metadata and schema generation
- slugs
- database schema and migrations
- validation, business rules, and resources
- background jobs and scheduled tasks

## Current MVP Scope

- Admin can create, update, publish, unpublish, and delete posts.
- Posts are article-first and do not use templates or post blocks.
- Admin can manage categories, tags, media, knowledge base, and site settings.
- Public frontend consumes published content from the service.
- AI workflows are service-driven and draft-first.

### AI Content Engine MVP

- Knowledge Base entries ground AI workflows.
- `TopicDiscoveryAgent` creates scored topics in approved clusters.
- topics below `90` are pruned automatically.
- topics above `90` queue blog draft generation automatically.
- `BlogWriterAgent` generates one full article draft.
- all AI-generated posts remain `draft` until manual admin approval.
- images remain manual; AI may only suggest ideas, placement notes, and alt text.
- prompts are database-backed and versioned.
- the editable main-flow prompt families are `topic_standard` and `blog_standard`.

### Approved Topic Clusters

- `ai_tools`
- `ai_for_blogging`
- `seo`
- `content_marketing`
- `productivity_automation`
- `developer_ai`

## Non-Goals For Service Agent

- admin UI implementation unless explicitly requested
- public frontend UI implementation unless explicitly requested
- reintroducing templates, content briefs, or block composition
- unrequested package changes
- direct publishing by AI

## Technology Baseline

- Laravel 13
- Eloquent and migrations
- Pest for tests
- Cloudflare R2 for media storage
- provider-agnostic AI client abstractions
- database-backed queue and cache defaults

## Product Constraints

- AI-generated content must never publish directly.
- AI-generated drafts require explicit admin review.
- retries must not duplicate topics or posts.
- long-running AI work uses the explicit `ai` queue.
- prompt ownership for topic/blog generation lives in versioned prompt templates, not `site_settings`.
