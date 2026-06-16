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

## Expansion Modules

- Topics: discovery queue and editorial intake
- AI Jobs: provider-backed generation and analysis workflow tracking
- AI Cost Tracking: provider usage, token, and cost visibility
- Internal Linking: suggestion and graph analysis
- Analytics: content performance signals

## Boundary Rules

- Posts own publish lifecycle.
- Templates shape post structure and rendering behavior.
- Post Blocks own content composition, not templates.
- SEO Metadata augments posts and pages without owning content.
- Knowledge Base supports authoring and AI workflows without becoming public article content by default.
- Media is a shared asset service, not post-only storage.
