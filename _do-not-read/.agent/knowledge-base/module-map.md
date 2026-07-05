# Module Map

## Purpose

Use this file to map backend responsibilities before reading deeper docs or code.

## Core Service Modules

- Auth: admin authentication, guards, tokens, permissions
- Categories: taxonomy for content organization
- Tags: secondary post classification
- Posts: main publishing entity and lifecycle owner
- Media: upload, metadata, attribution, usage tracking, R2 integration
- Knowledge Base: reusable factual, experiential, and research assets
- SEO Metadata: titles, descriptions, schema inputs, canonical decisions, scoring inputs
- Content Topics: editorial intake queue for AI-suggested or manually added topics
- Site Settings: singleton CMS settings such as footer content

## AI And Workflow Modules

- AI Prompt Templates: database-backed prompt definitions, variables, schemas, and active versions for the standard prompt families
- AI Jobs: workflow-level generation and analysis tracking
- AI Generation Steps: per-agent execution tracking inside a job
- AI Cost Tracking: provider usage, token, and cost visibility
- AI Agents: topic discovery, draft writing, and fixed helper suggestion agents
- AI Workflow Orchestration: services coordinating validation, prompts, agents, persistence, and retries
- AI Tools: internal helpers for duplicate checks, persistence, internal links, and content lookup
- Internal Linking: suggestion and graph analysis

## Boundary Rules

- Posts own publish lifecycle.
- Posts are article-first and do not depend on templates or block collections.
- SEO Metadata augments posts and pages without owning content.
- Knowledge Base supports authoring and AI workflows without becoming public article content by default.
- Media is a shared asset service, not post-only storage.
- Content Topics may be AI-suggested, but publishing decisions are editorial.
- AI Jobs track execution state, not publish authorization state.
