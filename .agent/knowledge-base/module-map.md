# Module Map

## Purpose

Use this file to map backend responsibilities before reading deeper docs or code.

## Core Service Modules

- Auth: admin authentication, guards, tokens, permissions
- Categories: taxonomy for content organization
- Tags: cross-category topic labeling
- Posts: main publishing entity and lifecycle owner
- Post Blocks: structured content storage for renderable article sections
- Templates: predefined rendering and content structure rules
- Media: upload, metadata, attribution, usage tracking, R2 integration
- Knowledge Base: reusable factual, experiential, and research assets
- SEO Metadata: titles, descriptions, schema inputs, canonical decisions, scoring inputs
- Content Topics: editorial intake queue for AI-suggested or manually added topics
- Content Briefs: structured article plans generated from approved topics

## Expansion Modules

- AI Prompt Templates: database-backed prompt definitions, variables, schemas, and active versions
- AI Jobs: workflow-level generation and analysis tracking
- AI Generation Steps: per-agent execution tracking inside a job
- AI Cost Tracking: provider usage, token, and cost visibility
- AI Agents: topic discovery, content brief generation, and draft writing agents
- AI Workflow Orchestration: services coordinating validation, prompts, agents, persistence, and retries
- AI Tools: internal helpers for duplicate checks, save operations, internal links, and content lookup
- Internal Linking: suggestion and graph analysis
- Analytics: content performance signals

## Boundary Rules

- Posts own publish lifecycle.
- Templates shape post structure and rendering behavior.
- Post Blocks own content composition, not templates.
- SEO Metadata augments posts and pages without owning content.
- Knowledge Base supports authoring and AI workflows without becoming public article content by default.
- Media is a shared asset service, not post-only storage.
- Content Topics may be AI-suggested, but approval is editorial.
- Content Briefs are intermediary planning assets, not publishable content.
- AI Jobs track execution state, not editorial approval state.
